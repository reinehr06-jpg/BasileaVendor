<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda Confirmada</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; margin-top: 40px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #581c87, #7c3aed); padding: 40px 30px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 28px;">🎉 Venda Confirmada!</h1>
                <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 16px;">Parabéns! Sua venda foi concretizada com sucesso.</p>
            </td>
        </tr>
        
        <!-- Content -->
        <tr>
            <td style="padding: 30px;">
                <p style="font-size: 16px; color: #333; line-height: 1.6;">
                    Olá <strong>{{ $venda->vendedor->user->name ?? 'Vendedor' }}</strong>,
                </p>
                <p style="font-size: 16px; color: #333; line-height: 1.6;">
                    Uma nova venda foi confirmada! Veja os detalhes abaixo:
                </p>
                
                <!-- Sale Details Box -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin: 20px 0;">
                    <table width="100%" cellpadding="8">
                        <tr>
                            <td style="font-weight: bold; color: #581c87; width: 140px;">Igreja:</td>
                            <td>{{ $nomeIgreja }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #581c87;">Responsável:</td>
                            <td>{{ $nomeResponsavel }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #581c87;">Plano:</td>
                            <td>{{ $venda->plano ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #581c87;">Valor Pago:</td>
                            <td style="font-size: 20px; font-weight: bold; color: #16a34a;">R$ {{ $valor }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #581c87;">Forma Pgto:</td>
                            <td>{{ $formaPagamento }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #581c87;">Data Pgto:</td>
                            <td>{{ $dataPagamento }}</td>
                        </tr>
                        @if($venda->comissao_gerada)
                        <tr>
                            <td style="font-weight: bold; color: #581c87;">Comissão:</td>
                            <td style="font-size: 18px; font-weight: bold; color: #7c3aed;">R$ {{ number_format($venda->comissao_gerada, 2, ',', '.') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                
                <!-- CTA Button -->
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $linkVenda }}" style="display: inline-block; background: #581c87; color: white; padding: 14px 40px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 16px;">
                        👁️ Ver Detalhes da Venda
                    </a>
                </div>
                
                <p style="font-size: 14px; color: #666; text-align: center;">
                    Acesse o sistema para visualizar todos os detalhes e acompanhar suas comissões.
                </p>
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                <p style="font-size: 12px; color: #94a3b8; margin: 0;">
                    Basilélia Vendas - Sistema de Gestão Comercial<br>
                    Este é um email automático, não responda.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>