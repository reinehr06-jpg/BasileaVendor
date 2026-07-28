import http from 'k6/http';
import { check, sleep } from 'k6';

// Configurações de Carga (Load Test Profile)
export const options = {
  stages: [
    { duration: '30s', target: 20 },  // Ramp-up: sobe para 20 usuários virtuais em 30s
    { duration: '1m', target: 20 },   // Plateau: mantém 20 usuários por 1 minuto
    { duration: '30s', target: 0 },   // Ramp-down: desce para 0 usuários em 30s
  ],
  thresholds: {
    // 95% das requisições devem ser completadas em menos de 500ms
    http_req_duration: ['p(95)<500'],
    // Taxa de erro deve ser menor que 1%
    http_req_failed: ['rate<0.01'],
  },
};

const BASE_URL = __ENV.API_URL || 'http://localhost:8000/api';

export default function () {
  // Teste 1: Healthcheck (Simula tráfego leve constante de monitoramento)
  let healthRes = http.get(`${BASE_URL}/health`);
  check(healthRes, {
    'healthcheck status is 200': (r) => r.status === 200,
  });

  // Teste 2: Tentativa de login maliciosa/falha (Testa o rate limiter e o banco)
  const loginPayload = JSON.stringify({
    email: 'stress-test@basileia.global',
    password: 'wrong_password',
  });
  const headers = { 'Content-Type': 'application/json' };
  
  let loginRes = http.post(`${BASE_URL}/login`, loginPayload, { headers });
  
  // Como a senha está errada, esperamos um 401. Se voltar 429, o Rate Limiter está funcionando.
  check(loginRes, {
    'login falhou como esperado (401 ou 429)': (r) => r.status === 401 || r.status === 429,
  });

  sleep(1); // Espera 1 segundo entre as iterações do usuário
}
