<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo à Basiléia Church</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; margin-top: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #581c87, #7c3aed); padding: 40px 30px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 28px;">🎉 Bem-vindo à Basiléia Church!</h1>
                <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 16px;">Sua compra foi confirmada com sucesso.</p>
            </td>
        </tr>
        
        <!-- Welcome Message -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333; line-height: 1.6;">
                    Olá <strong>{{ $cliente->nome_igreja ?? $cliente->nome ?? 'Cliente' }}</strong>,
                </p>
                <p style="font-size: 16px; color: #333; line-height: 1.6;">
                    Seja muito bem-vindo(a) à família Basiléia Church! Sua compra do <strong>Plano {{ $venda->plano ?? 'Basiléia' }}</strong> foi confirmada e seu sistema já está liberado para uso.
                </p>
            </td>
        </tr>
        
        <!-- Login Credentials -->
        <tr>
            <td style="padding: 0 30px 30px;">
                <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #f59e0b; border-radius: 10px; padding: 20px;">
                    <h3 style="color: #92400e; margin: 0 0 15px; font-size: 16px;">🔐 Seus dados de acesso:</h3>
                    <table width="100%" cellpadding="5">
                        <tr>
                            <td style="color: #92400e; font-weight: bold;">E-mail:</td>
                            <td style="color: #78350f;">{{ $cliente->email ?? 'Não informado' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #92400e; font-weight: bold;">Telefone:</td>
                            <td style="color: #78350f;">{{ $cliente->whatsapp ?? $cliente->telefone ?? 'Não informado' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #92400e; font-weight: bold;">Senha:</td>
                            <td style="color: #78350f; font-family: monospace; font-size: 18px; font-weight: bold;">{{ $senha }}</td>
                        </tr>
                    </table>
                    <p style="font-size: 12px; color: #b45309; margin: 15px 0 0;">
                        💡 Dica: Use seu e-mail ou telefone para fazer login no sistema.
                    </p>
                </div>
            </td>
        </tr>
        
        <!-- Action Buttons -->
        <tr>
            <td style="padding: 0 30px 30px;">
                <h3 style="color: #333; margin: 0 0 20px; font-size: 18px;">📌 Acesse agora mesmo:</h3>
                
                <!-- Button 1: Login -->
                <div style="margin-bottom: 15px;">
                    <a href="{{ $linkLogin }}" style="display: block; background: #581c87; color: white; padding: 16px 24px; border-radius: 10px; text-decoration: none; text-align: center;">
                        <strong style="font-size: 18px;">🚀 Acessar o Sistema</strong><br>
                        <span style="font-size: 13px; opacity: 0.9;">Faça login para começar a usar sua Basiléia Church</span>
                    </a>
                </div>
                
                <!-- Button 2: Videos -->
                <div style="margin-bottom: 15px;">
                    <a href="{{ $linkVideos }}" style="display: block; background: #2563eb; color: white; padding: 16px 24px; border-radius: 10px; text-decoration: none; text-align: center;">
                        <strong style="font-size: 18px;">🎬 Vídeos de Implementação</strong><br>
                        <span style="font-size: 13px; opacity: 0.9;">Aprenda a configurar e usar o sistema com nossos tutoriais</span>
                    </a>
                </div>
                
                <!-- Button 3: Support -->
                <div style="margin-bottom: 15px;">
                    <a href="{{ $linkSuporte }}" style="display: block; background: #16a34a; color: white; padding: 16px 24px; border-radius: 10px; text-decoration: none; text-align: center;">
                        <strong style="font-size: 18px;">💬 Fale com o Suporte</strong><br>
                        <span style="font-size: 13px; opacity: 0.9;">Precisa de ajuda? Nossa equipe está pronta para atender você</span>
                    </a>
                </div>
            </td>
        </tr>
        
        <!-- Purchase Summary -->
        <tr>
            <td style="padding: 0 30px 30px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                    <h3 style="color: #581c87; margin: 0 0 15px; font-size: 16px;">📋 Resumo da sua compra:</h3>
                    <table width="100%" cellpadding="5">
                        <tr>
                            <td style="color: #666;">Plano:</td>
                            <td style="font-weight: bold; text-align: right;">{{ $venda->plano ?? 'Basiléia' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666;">Tipo:</td>
                            <td style="font-weight: bold; text-align: right;">{{ ucfirst($venda->tipo_negociacao ?? 'Mensal') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #666;">Valor:</td>
                            <td style="font-weight: bold; color: #16a34a; text-align: right; font-size: 18px;">R$ {{ number_format($venda->valor_final ?? $venda->valor, 2, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                <p style="font-size: 12px; color: #94a3b8; margin: 0;">
                    Basiléia Church - Sistema de Gestão Eclesiástica<br>
                    Este é um email automático, não responda.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
