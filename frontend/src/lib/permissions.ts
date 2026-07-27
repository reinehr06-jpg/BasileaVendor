export const permissions = {
  dashboard: ['master', 'gestor', 'vendedor'],
  vendas: ['master', 'gestor', 'vendedor'],
  clientes: ['master', 'gestor', 'vendedor'],
  comissoes: ['master', 'gestor', 'vendedor'],
  equipes: ['master', 'gestor'],
  configuracoes: ['master', 'gestor', 'vendedor'],
};

export function hasPermission(perfil: string, rota: string): boolean {
  const allowed = permissions[rota as keyof typeof permissions];
  return allowed ? allowed.includes(perfil) : false;
}
