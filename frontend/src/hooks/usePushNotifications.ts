"use client";

import { useState, useEffect, useCallback } from "react";

/**
 * Hook para gerenciar Push Notifications via Web Push API.
 * 
 * Retorna:
 * - `isSupported`: se o navegador suporta Push Notifications
 * - `permission`: estado atual da permissão ("granted", "denied", "default")
 * - `requestPermission()`: solicita permissão ao usuário
 * - `subscription`: objeto de inscrição push (se existir)
 */
export function usePushNotifications() {
  const [isSupported, setIsSupported] = useState(false);
  const [permission, setPermission] = useState<NotificationPermission>("default");
  const [subscription, setSubscription] = useState<PushSubscription | null>(null);

  useEffect(() => {
    const supported = "Notification" in window && "serviceWorker" in navigator && "PushManager" in window;
    setIsSupported(supported);

    if (supported) {
      setPermission(Notification.permission);
    }
  }, []);

  const requestPermission = useCallback(async () => {
    if (!isSupported) return;

    try {
      const result = await Notification.requestPermission();
      setPermission(result);

      if (result === "granted") {
        // Obtém a inscrição push do Service Worker
        const registration = await navigator.serviceWorker.ready;
        const existingSub = await registration.pushManager.getSubscription();

        if (existingSub) {
          setSubscription(existingSub);
        } else {
          // Para produção, substitua por sua VAPID public key real
          const VAPID_PUBLIC_KEY = process.env.NEXT_PUBLIC_VAPID_PUBLIC_KEY || "";
          
          if (VAPID_PUBLIC_KEY) {
            const newSub = await registration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
            });
            setSubscription(newSub);

            // Envia a inscrição para o backend
            await fetch("/api/push/subscribe", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(newSub.toJSON()),
            });
          }
        }
      }
    } catch (err) {
      console.error("Erro ao solicitar permissão de notificação:", err);
    }
  }, [isSupported]);

  return { isSupported, permission, requestPermission, subscription };
}

/**
 * Converte uma chave VAPID em base64 para Uint8Array (requisito da Web Push API).
 */
function urlBase64ToUint8Array(base64String: string): Uint8Array {
  const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}
