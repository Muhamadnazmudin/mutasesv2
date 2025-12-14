const CACHE_NAME = "mutases-v1";

const urlsToCache = [
	"/mutases/",
	"/mutases/assets/css/bootstrap.min.css",
	"/mutases/assets/js/bootstrap.bundle.min.js",
];

self.addEventListener("install", (event) => {
	event.waitUntil(
		caches
			.open(CACHE_NAME)
			.then((cache) => cache.addAll(urlsToCache))
			.then(() => self.skipWaiting())
	);
});

self.addEventListener("activate", (event) => {
	event.waitUntil(self.clients.claim());
});

self.addEventListener("fetch", (event) => {
	event.respondWith(
		caches
			.match(event.request)
			.then((response) => response || fetch(event.request))
	);
});
