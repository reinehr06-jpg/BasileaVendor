<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Confirmada</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; margin-top: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #581c87, #7c3aed); padding: 40px 30px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 28px;">🎉 Compra Confirmada!</h1>
                <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 16px;">Sua aquisição foi realizada com sucesso.</p>
            </td>
        </tr>
        
        <!-- Welcome Message -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333; line-height: 1.6;">
                    Olá <strong>{{ $venda->cliente->nome ?? $venda->cliente->nome_igreja ?? 'Cliente' }}</strong>,
                </p>
                <p style="font-size: 16px; color: #333; line-height: 1.6;">
                    Sua compra do <strong>Plano {{ $plano }}</strong> foi confirmada! O sistema já está liberado para você utilizar.
                </p>
            </td>
        </tr>
        
        <!-- Purchase Summary -->
        <tr>
            <td style="padding: 0 30px 30px;">
                <div style="background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #f59e0b; border-radius: 10px; padding: 20px;">
                    <h3 style="color: #92400e; margin: 0 0 15px; font-size: 16px;">📋 Resumo da Compra:</h3>
                    <table width="100%" cellpadding="5">
                        <tr>
                            <td style="color: #92400e; font-weight: bold;">Plano:</td>
                            <td style="color: #78350f; text-align: right; font-weight: bold;">{{ $plano }}</td>
                        </tr>
                        <tr>
                            <td style="color: #92400e; font-weight: bold;">Tipo:</td>
                            <td style="color: #78350f; text-align: right;">{{ ucfirst($venda->tipo_negociacao ?? 'Mensal') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #92400e; font-weight: bold;">Valor Pago:</td>
                            <td style="color: #16a34a; text-align: right; font-weight: bold; font-size: 20px;">R$ {{ $valorPago }}</td>
                        </tr>
                        @if($venda->parcelas > 1)
                        <tr>
                            <td style="color: #92400e; font-weight: bold;">Parcelas:</td>
                            <td style="color: #78350f; text-align: right;">{{ $venda->parcelas }}x</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
        
        <!-- Action Buttons -->
        <tr>
            <td style="padding: 0 30px 30px;">
                <h3 style="color: #333; margin: 0 0 20px; font-size: 18px;">📌 Acesse agora:</h3>
                
                <!-- Button 1: Login -->
                <div style="margin-bottom: 15px;">
                    <a href="{{ $linkLogin }}" style="display: block; background: #581c87; color: white; padding: 16px 24px; border-radius: 10px; text-decoration: none; text-align: center;">
                        <strong style="font-size: 18px;">🚀 Acessar o Sistema</strong><br>
                        <span style="font-size: 13px; opacity: 0.9;">Faça login para começar a usar sua Basilélia Church</span>
                    </a>
                </div>
                
                <!-- Button 2: Videos -->
                <div style="margin-bottom: 15px;">
                    <a href="{{ $linkVideos }}" style="display: block; background: #2563eb; color: white; padding: 16px 24px; border-radius: 10px; text-decoration: none; text-align: center;">
                        <strong style="font-size: 18px;">🎬 Vídeos de Implementação</strong><br>
                        <span style="font-size: 13px; opacity: 0.9;">Aprenda a configurar e usar o sistema com nossos tutoriais</span>
                    </a>
                </div>
            </td>
        </tr>
        
        <!-- Terms & Invoice -->
        <tr>
            <td style="padding: 0 30px 30px;">
                @if($linkTermos || $notaFiscalUrl)
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px;">
                    <h3 style="color: #581c87; margin: 0 0 15px; font-size: 16px;">📄 Documentos:</h3>
                    <table width="100%" cellpadding="5">
                        @if($linkTermos)
                        <tr>
                            <td>
                                <a href="{{ $linkTermos }}" style="display: inline-block; background: #64748b; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px;">
                                    📥 Baixar Termos (PDF)
                                </a>
                            </td>
                        </tr>
                        @endif
                        @if($notaFiscalUrl)
                        <tr>
                            <td>
                                <a href="{{ $notaFiscalUrl }}" style="display: inline-block; background: #16a34a; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px;">
                                    🧾 Ver Nota Fiscal
                                </a>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
                @endif
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                <p style="font-size: 12px; color: #94a3b8; margin: 0;">
                    Basilélia Church - Sistema de Gestão Eclesiástica<br>
                    Este é um email automático, não responda.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>