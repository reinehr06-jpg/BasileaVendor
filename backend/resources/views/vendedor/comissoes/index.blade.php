<div>
    <h1>TESTE DE VIEW OK</h1>
    <p>Se você está vendo isso, o problema é o LAYOUT ou CSS/JS da página.</p>
    <pre>
        Vendedor ID: {{ $vendedor->id ?? 'N/A' }}
        Mes: {{ $mes }}
        Comissoes: {{ count($comissoes) }}
    </pre>
</div>
