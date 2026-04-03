const API = "http://127.0.0.1:8000/api";

function request(url, method, body = null) {
    return fetch(API + url, {
        method: method,
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: body ? JSON.stringify(body) : null
    }).then(res => res.json());
}