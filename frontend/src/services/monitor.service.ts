import { api } from "@/lib/api";

export const MonitorService = {
  logs: async () => {
    return new Promise<any>((resolve) => {
      setTimeout(() => {
        resolve({
          data: {
            data: [
              {
                id: "evt_109283019",
                timestamp: "10:45:22 05/07/2026",
                event: "PAYMENT_RECEIVED",
                source: "Asaas",
                status: 200,
                customer: "Tabernáculo Church",
                amount: "R$ 197,00",
                payload: {
                  "event": "PAYMENT_RECEIVED",
                  "payment": {
                    "id": "pay_982374982374",
                    "customer": "cus_000005123412",
                    "value": 197.00,
                    "netValue": 191.09,
                    "billingType": "CREDIT_CARD",
                    "status": "RECEIVED"
                  }
                }
              },
              {
                id: "evt_109283020",
                timestamp: "10:42:15 05/07/2026",
                event: "SUBSCRIPTION_CREATED",
                source: "Asaas",
                status: 200,
                customer: "Igreja Batista Central",
                amount: "R$ 299,00",
                payload: {
                  "event": "SUBSCRIPTION_CREATED",
                  "subscription": {
                    "id": "sub_4985734958",
                    "customer": "cus_000005123499",
                    "value": 299.00,
                    "cycle": "MONTHLY",
                    "status": "ACTIVE"
                  }
                }
              },
              {
                id: "evt_109283021",
                timestamp: "10:30:05 05/07/2026",
                event: "WEBHOOK_FAILED",
                source: "System",
                status: 500,
                customer: "Desconhecido",
                amount: "-",
                payload: {
                  "error": "Timeout connection to Database",
                  "code": 504,
                  "stack": "Error: Timeout connection... at /src/services/webhook.js:45"
                }
              },
              {
                id: "evt_109283022",
                timestamp: "09:15:44 05/07/2026",
                event: "PAYMENT_REFUNDED",
                source: "Stripe",
                status: 200,
                customer: "Comunidade da Graça",
                amount: "R$ 50,00",
                payload: {
                  "event": "charge.refunded",
                  "data": {
                    "object": {
                      "id": "ch_3M4X...",
                      "amount_refunded": 5000,
                      "status": "succeeded"
                    }
                  }
                }
              }
            ]
          }
        });
      }, 500);
    });
  }
};
