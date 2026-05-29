self.addEventListener("install", function (event) {
    console.log("Service Worker installé");
});

self.addEventListener("fetch", function (event) {
    // mode simple (on ne cache rien pour l’instant)
});