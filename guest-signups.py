from flask import Flask, request, Response
import requests

app = Flask(__name__)

# Target URL
TARGET_URL = "https://prod.api.indusgame.com/guest-signups"

@app.route("/", defaults={"path": ""}, methods=["GET", "POST", "PUT", "DELETE", "PATCH"])
@app.route("/<path:path>", methods=["GET", "POST", "PUT", "DELETE", "PATCH"])
def proxy(path):
    # Construct target URL (with same path if needed)
    url = f"{TARGET_URL}"

    # Capture headers (excluding Host)
    headers = {key: value for key, value in request.headers if key.lower() != "host"}

    # Forward request to target
    resp = requests.request(
        method=request.method,
        url=url,
        headers=headers,
        data=request.get_data(),
        cookies=request.cookies,
        allow_redirects=False,
    )

    # Build response for client
    excluded_headers = ["content-encoding", "transfer-encoding", "connection"]
    response_headers = [(name, value) for name, value in resp.headers.items() if name.lower() not in excluded_headers]

    return Response(resp.content, resp.status_code, response_headers)


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
