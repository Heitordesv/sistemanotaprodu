self.addEventListener('install', function(event) {
  self.skipWaiting();
});

self.addEventListener('activate', function(event) {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function(event) {
  // Mantido sem estratégia de cache para não interferir no ERP.
});

self.addEventListener('push', function(event) {
  let data = {};

  try {
    data = event.data ? event.data.json() : {};
  } catch (e) {
    data = {
      title: 'NFe Notas',
      body: event.data ? event.data.text() : 'Você recebeu uma nova notificação.'
    };
  }

  const title = data.title || 'NFe Notas';
  const options = {
    body: data.body || 'Você recebeu uma nova mensagem.',
    icon: data.icon || '/favicon.ico',
    badge: data.badge || '/favicon.ico',
    tag: data.tag || 'nfenotas-support',
    renotify: true,
    vibrate: [180, 80, 180],
    data: {
      url: data.url || '/',
      ticket_id: data.ticket_id || null
    }
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  const targetUrl = event.notification.data && event.notification.data.url
    ? event.notification.data.url
    : '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
      for (const client of clientList) {
        if ('focus' in client) {
          client.navigate(targetUrl);
          return client.focus();
        }
      }

      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
    })
  );
});