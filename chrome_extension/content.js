(function () {
    // Avoid injecting twice
    if (window.__sendUrlButtonAdded) return;
    window.__sendUrlButtonAdded = true;

    const button = document.createElement("button");
    button.textContent = "Send URL";
    Object.assign(button.style, {
        position: "fixed",
        bottom: "20px",
        right: "20px",
        padding: "10px 14px",
        background: "#444",
        color: "white",
        border: "none",
        borderRadius: "6px",
        cursor: "pointer",
        zIndex: 999999
    });

    button.onclick = function () {
        const payload = { url: window.location.href };

        let URL = "http://local.bristolian.org//api/extension_test";

        fetch(URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
            .then((res) => res.text())
            .then((txt) => {
                console.log("Server response:", txt);
            })
            .catch((err) => {
                console.error("Error sending URL:", err);
            });
    };

    document.body.appendChild(button);
})();