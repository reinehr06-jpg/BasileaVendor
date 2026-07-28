// ==============================================================================
// Basiléia Vendor OS — Service Worker
// Estratégia: Cache-First para assets estáticos, Network-First para API.
// ==============================================================================

const CACHE_NAME = "basileia-vendor-v1";
const STATIC_ASSETS = [
  "/",
  "/manifest.json",
];

// Instalação: pré-cacheia assets essenciais
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  // Força ativação imediata (sem esperar abas antigas fecharem)
  self.skipWaiting();
});

// Ativação: limpa caches antigos
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      );
    })
  );
  // Toma controle de todas as abas abertas imediatamente
  self.clients.claim();
});

// Fetch: estratégia Network-First para /api, Cache-First para o resto
self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Rotas de API: sempre tenta a rede primeiro
  if (url.pathname.startsWith("/api/")) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Clona e cacheia respostas GET bem-sucedidas
          if (request.method === "GET" && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // Sem rede? Tenta o cache como fallback
          return caches.match(request);
        })
    );
    return;
  }

  // Todos os outros recursos: Cache-First
  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      return fetch(request).then((response) => {
        // Cacheia a resposta para futuras requisições
        if (request.method === "GET" && response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, responseClone);
          });
        }
        return response;
      });
    })
  );
});

// Push Notifications: exibe notificação quando recebida
self.addEventListener("push", (event) => {
  const data = event.data ? event.data.json() : {};
  const title = data.title || "Basiléia Vendor OS";
  const options = {
    body: data.body || "Você tem uma nova notificação.",
    icon: "/icons/icon-192x192.png",
    badge: "/icons/icon-72x72.png",
    vibrate: [100, 50, 100],
    data: {
      url: data.url || "/",
    },
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

// Clique na notificação: abre a URL correspondente
self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || "/";

  event.waitUntil(
    self.clients.matchAll({ type: "window" }).then((clients) => {
      // Se já existe uma aba aberta, foca nela
      for (const client of clients) {
        if (client.url.includes(targetUrl) && "focus" in client) {
          return client.focus();
        }
      }
      // Caso contrário, abre nova aba
      return self.clients.openWindow(targetUrl);
    })
  );
});
