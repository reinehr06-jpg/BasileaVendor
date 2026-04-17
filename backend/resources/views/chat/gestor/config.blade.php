@extends('chat.layout')

@section('chat-content')
<div class="chat-main" style="padding: 20px;">
    <h4><i class="fab fa-whatsapp me-2"></i>Configurações do WhatsApp</h4>
    <hr>

    <form method="POST" action="{{ route('gestor.chat.config.update') }}">
        @csrf
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Número de Telefone</label>
                <input type="text" name="numero_telefone" class="form-control" 
                       value="{{ $config->numero_telefone ?? '' }}" 
                       placeholder="+55119999999999">
                <small class="text-muted">Número com código do país</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Provider</label>
                <select name="provider" class="form-control">
                    <option value="">Selecione...</option>
                    <option value="meta" @if(($config->provider ?? '') === 'meta') selected @endif>Meta (Facebook)</option>
                    <option value="Take" @if(($config->provider ?? '') === 'Take') selected @endif>Take (TakeBlip)</option>
                    <option value="WppConnect" @if(($config->provider ?? '') === 'WppConnect') selected @endif>WppConnect</option>
                    <option value="Evolution" @if(($config->provider ?? '') === 'Evolution') selected @endif>Evolution API</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">API Token</label>
            <input type="password" name="api_token" class="form-control" 
                   value="{{ $config->api_token ?? '' }}" 
                   placeholder="Token de acesso à API">
        </div>

        <div class="mb-3">
            <label class="form-label">Webhook Verify Token</label>
            <div class="input-group">
                <input type="text" name="webhook_verify_token" class="form-control" 
                       value="{{ $config->webhook_verify_token ?? '' }}" 
                       placeholder="Token para verificar webhook">
                <button type="button" class="btn btn-outline-secondary" onclick="this.previousElementSibling.value = Math.random().toString(36).substring(2)">
                    <i class="fas fa-random"></i> Gerar
                </button>
            </div>
            <small class="text-muted">Use este token para configurar o webhook no provider</small>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" 
                       @if(($config->is_active ?? false)) checked @endif>
                <label class="form-check-label" for="isActive">
                    Ativar integração
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i> Salvar Configuração
        </button>
    </form>

    <hr>
    <h5>URLs de Webhook</h5>
    <div class="alert alert-info">
        <strong>Google Ads:</strong> <code>{{ url('/webhooks/chat/google-ads') }}</code><br>
        <strong>Meta Leads:</strong> <code>{{ url('/webhooks/chat/meta-leads') }}</code><br>
        <strong>WhatsApp:</strong> <code>{{ url('/webhooks/chat/whatsapp') }}</code>
    </div>
    <p class="text-muted">Configure estas URLs nos respectivos painéis de integração.</p>
</div>
@endsection