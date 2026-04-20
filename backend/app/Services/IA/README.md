# Sistema de IA Local para Primeira Mensagem

## Configuração

### 1. Instalar Ollama (Recomendado)
```bash
# Linux/Mac
curl -fsSL https://ollama.ai/install.sh | sh

# Windows
# Baixe e instale: https://ollama.ai/download
```

### 2. Instalar Modelo
```bash
ollama pull llama3.2
# ou outro modelo de sua preferência
```

### 3. Iniciar Servidor
```bash
ollama serve
```

### 4. Configurar no .env
```env
IA_LOCAL_ENDPOINT=http://localhost:11434/api/generate
IA_LOCAL_MODEL=llama3.2
```

## Como Usar

### No Controller
```php
use App\Services\IA\PrimeiraMensagemIAService;

$ia = new PrimeiraMensagemIAService();
$sugestoes = $ia->gerarSugestoes("Cliente interessado em plano premium para igreja", 5);
```

### Na View
```blade
@foreach($sugestoes as $sugestao)
    <div class="sugestao">{{ $sugestao }}</div>
@endforeach
```

## Funcionalidades

- **Geração automática** de sugestões de primeira mensagem
- **Configurável** por endpoint e modelo
- **Timeout de 90 segundos** para evitar travamentos
- **Limitação de caracteres** (máx. 160 por mensagem)
- **Tratamento de erros** com logs detalhados

## Modelos Recomendados

- `llama3.2` - Rápido e bom para português
- `llama3.1:8b` - Melhor qualidade, mais lento
- `mistral` - Boa alternativa open-source

## Troubleshooting

### Erro de conexão
- Verifique se o Ollama está rodando: `ollama serve`
- Confirme o endpoint no .env

### Modelo não encontrado
- Instale o modelo: `ollama pull llama3.2`
- Verifique nome no .env

### Timeout
- Aumente o timeout no Http::timeout()
- Use modelo menor para respostas mais rápidas