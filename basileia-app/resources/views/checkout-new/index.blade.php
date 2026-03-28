<!DOCTYPE html>
<html lang="{{ $language ?? 'pt-BR' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('checkout_new.page_title', ['offer' => $pricing['offer']['name']]) }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('checkout_new.meta_description') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #6D28D9;
            --primary-dark: #5B21B6;
            --primary-light: #EDE9FE;
            --success: #10B981;
            --success-bg: #D1FAE5;
            --warning: #F59E0B;
            --danger: #EF4444;
            --white: #FFFFFF;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            --radius: 16px;
            --radius-sm: 10px;
            --radius-lg: 24px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #6D28D9 0%, #7C3AED 50%, #5B21B6 100%);
            min-height: 100vh;
            color: var(--gray-800);
            -webkit-font-smoothing: antialiased;
        }

        /* ═══════════════════════════════════════════════════════════════
           URGENCY BANNER - Countdown Timer
           ═══════════════════════════════════════════════════════════════ */
        .urgency-banner {
            background: linear-gradient(90deg, #DC2626 0%, #EA580C 50%, #D97706 100%);
            color: white;
            padding: 14px 20px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            position: relative;
            overflow: hidden;
        }

        .urgency-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 200%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(50%); }
        }

        .urgency-timer {
            background: rgba(0,0,0,0.3);
            padding: 6px 14px;
            border-radius: 8px;
            font-family: 'Inter', monospace;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .urgency-spots {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        /* ═══════════════════════════════════════════════════════════════
           HEADER - Logo + Language Selector
           ═══════════════════════════════════════════════════════════════ */
        .header {
            background: rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            height: 45px;
            filter: brightness(0) invert(1);
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .lang-selector {
            position: relative;
        }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .lang-btn:hover {
            background: rgba(255,255,255,0.25);
        }

        .lang-btn .flag {
            font-size: 1.4rem;
        }

        .lang-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 320px;
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            display: none;
            z-index: 100;
            overflow: hidden;
        }

        .lang-dropdown.active {
            display: block;
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .lang-search {
            padding: 12px;
            border-bottom: 1px solid var(--gray-100);
        }

        .lang-search input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .lang-search input:focus {
            border-color: var(--primary);
        }

        .lang-list {
            max-height: 280px;
            overflow-y: auto;
        }

        .lang-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .lang-item:hover {
            background: var(--gray-50);
        }

        .lang-item.selected {
            background: var(--primary-light);
        }

        .lang-item .flag {
            font-size: 1.8rem;
        }

        .lang-item .info { flex: 1; }

        .lang-item .name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--gray-800);
        }

        .lang-item .currency {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .lang-item .check {
            color: var(--primary);
            opacity: 0;
            font-size: 1.2rem;
        }

        .lang-item.selected .check { opacity: 1; }

        /* Security Badge */
        .secure-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .secure-badge i {
            color: #10B981;
        }

        /* ═══════════════════════════════════════════════════════════════
           MAIN CONTAINER
           ═══════════════════════════════════════════════════════════════ */
        .main {
            max-width: 1140px;
            margin: 0 auto;
            padding: 32px 20px 60px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 28px;
            align-items: start;
        }

        @media (max-width: 960px) {
            .main {
                grid-template-columns: 1fr;
                padding: 20px 16px 120px;
                gap: 20px;
            }
        }

        /* ═══════════════════════════════════════════════════════════════
           LEFT COLUMN - FORM
           ═══════════════════════════════════════════════════════════════ */
        .form-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 80px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        /* Progress Steps */
        .progress-bar {
            display: flex;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-100);
            padding: 20px 28px;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            position: relative;
        }

        .progress-step:not(:last-child)::after {
            content: '';
            flex: 1;
            height: 3px;
            background: var(--gray-200);
            margin: 0 16px;
            border-radius: 2px;
        }

        .progress-step.done::after {
            background: var(--success);
        }

        .progress-step.active::after {
            background: linear-gradient(90deg, var(--primary), var(--gray-200));
        }

        .step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            background: var(--gray-200);
            color: var(--gray-500);
            transition: all 0.3s;
        }

        .progress-step.active .step-circle {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 0 5px var(--primary-light);
        }

        .progress-step.done .step-circle {
            background: var(--success);
            color: white;
        }

        .step-text {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--gray-400);
        }

        .progress-step.active .step-text,
        .progress-step.done .step-text {
            color: var(--gray-800);
        }

        @media (max-width: 640px) {
            .step-text { display: none; }
        }

        /* Form Panels */
        .panel {
            display: none;
            padding: 32px;
            animation: fadeUp 0.3s ease;
        }

        .panel.active { display: block; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .panel-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 6px;
        }

        .panel-subtitle {
            color: var(--gray-500);
            font-size: 1rem;
            margin-bottom: 28px;
        }

        /* Form Fields */
        .field {
            margin-bottom: 20px;
        }

        .field-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        .field-input {
            width: 100%;
            padding: 16px 18px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            background: white;
        }

        .field-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .field-input::placeholder {
            color: var(--gray-400);
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 480px) {
            .field-row { grid-template-columns: 1fr; }
        }

        .field-hint {
            font-size: 0.8rem;
            color: var(--gray-500);
            margin-top: 6px;
        }

        /* ═══════════════════════════════════════════════════════════════
           PAYMENT METHODS
           ═══════════════════════════════════════════════════════════════ */
        .payment-methods {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .pay-tab {
            flex: 1;
            padding: 18px 12px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .pay-tab:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .pay-tab.active {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .pay-tab i {
            font-size: 1.6rem;
            margin-bottom: 6px;
            display: block;
            color: var(--gray-400);
        }

        .pay-tab.active i { color: var(--primary); }

        .pay-tab span {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--gray-600);
            display: block;
        }

        .pay-tab.active span { color: var(--primary); }

        .payment-panel {
            display: none;
        }

        .payment-panel.active { display: block; }

        /* PIX Display */
        .pix-display {
            text-align: center;
            padding: 28px;
            background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
            border-radius: var(--radius);
            border: 2px dashed var(--gray-200);
        }

        .pix-icon {
            width: 64px;
            height: 64px;
            background: var(--success-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .pix-icon i {
            font-size: 2rem;
            color: var(--success);
        }

        .pix-price {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--gray-900);
            margin-bottom: 8px;
        }

        .pix-badge {
            display: inline-block;
            background: var(--success-bg);
            color: #059669;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .pix-desc {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        /* Card Form */
        .card-form { display: none; }
        .card-form.active { display: block; }

        .card-brands {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .card-brands i {
            font-size: 2.2rem;
        }

        /* Installments */
        .installments-grid {
            display: grid;
            gap: 10px;
            margin-top: 20px;
        }

        .installment-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 18px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s;
        }

        .installment-option:hover {
            border-color: var(--primary);
        }

        .installment-option.active {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .installment-option .label {
            font-weight: 600;
            color: var(--gray-800);
        }

        .installment-option .value {
            font-weight: 700;
            color: var(--primary);
        }

        .installment-option .interest {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* Boleto */
        .boleto-display {
            text-align: center;
            padding: 32px;
            background: var(--gray-50);
            border-radius: var(--radius);
        }

        .boleto-display i {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 16px;
        }

        /* ═══════════════════════════════════════════════════════════════
           COUPON
           ═══════════════════════════════════════════════════════════════ */
        .coupon-row {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .coupon-row input {
            flex: 1;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .coupon-row input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .coupon-btn {
            padding: 14px 24px;
            background: var(--gray-800);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .coupon-btn:hover { background: var(--gray-900); }

        .coupon-success {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: var(--success-bg);
            color: #059669;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .coupon-success.show { display: flex; }

        .coupon-error {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 8px;
            display: none;
        }

        .coupon-error.show { display: block; }

        /* ═══════════════════════════════════════════════════════════════
           BUTTONS
           ═══════════════════════════════════════════════════════════════ */
        .btn {
            width: 100%;
            padding: 18px 24px;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            margin-top: 24px;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(109,40,217,0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-back {
            background: var(--gray-100);
            color: var(--gray-700);
            margin-top: 12px;
        }

        .btn-back:hover { background: var(--gray-200); }

        .btn .spinner {
            display: none;
            width: 22px;
            height: 22px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .btn.loading .spinner { display: block; }
        .btn.loading .btn-text { display: none; }

        /* Security Note */
        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .security-note i { color: var(--success); }

        /* ═══════════════════════════════════════════════════════════════
           RIGHT COLUMN - ORDER SUMMARY
           ═══════════════════════════════════════════════════════════════ */
        .summary {
            position: sticky;
            top: 20px;
        }

        .summary-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 25px 80px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .summary-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 28px;
            color: white;
        }

        .summary-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .summary-plan {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .summary-body { padding: 28px; }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .summary-row:last-child { border-bottom: none; }

        .summary-row .label {
            color: var(--gray-500);
            font-size: 0.95rem;
        }

        .summary-row .value {
            font-weight: 700;
            color: var(--gray-900);
        }

        .summary-row.original .value {
            text-decoration: line-through;
            color: var(--gray-400);
            font-weight: 500;
        }

        .discount-badge {
            display: inline-block;
            background: var(--success-bg);
            color: #059669;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .summary-row.total {
            padding-top: 20px;
            margin-top: 12px;
            border-top: 2px solid var(--gray-200);
            border-bottom: none;
        }

        .summary-row.total .label {
            font-weight: 700;
            color: var(--gray-900);
            font-size: 1.1rem;
        }

        .summary-row.total .value {
            font-size: 1.8rem;
            color: var(--primary);
            font-weight: 900;
        }

        /* Currency Badge */
        .currency-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        /* Benefits */
        .benefits {
            margin-top: 24px;
            padding: 20px;
            background: var(--gray-50);
            border-radius: var(--radius);
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            font-size: 0.9rem;
            color: var(--gray-700);
        }

        .benefit i {
            color: var(--success);
            font-size: 1.1rem;
        }

        /* Guarantee */
        .guarantee {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
            border-radius: var(--radius);
            border: 1px solid #FDE68A;
            display: flex;
            gap: 14px;
        }

        .guarantee-icon {
            width: 48px;
            height: 48px;
            background: #FDE68A;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .guarantee-icon i {
            font-size: 1.4rem;
            color: #B45309;
        }

        .guarantee-text h4 {
            font-weight: 700;
            color: #92400E;
            margin-bottom: 4px;
        }

        .guarantee-text p {
            font-size: 0.85rem;
            color: #B45309;
        }

        /* Social Proof */
        .social-proof {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            border-radius: var(--radius);
            border: 1px solid #A7F3D0;
            text-align: center;
        }

        .social-proof-title {
            font-weight: 600;
            color: #065F46;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .social-proof-number {
            font-size: 2rem;
            font-weight: 900;
            color: #065F46;
        }

        .social-proof-stars {
            color: #F59E0B;
            font-size: 1.1rem;
            margin-top: 6px;
        }

        .social-proof-stars span {
            color: var(--gray-500);
            font-size: 0.85rem;
            margin-left: 4px;
        }

        /* Trust Badges */
        .trust-row {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-100);
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .trust-item i {
            color: var(--success);
        }

        /* Features List */
        .features-list {
            margin-top: 20px;
            padding: 20px;
            background: var(--primary-light);
            border-radius: var(--radius);
        }

        .features-title {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.9rem;
            margin-bottom: 12px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            font-size: 0.85rem;
            color: var(--gray-700);
        }

        .feature-item i {
            color: var(--primary);
        }

        /* ═══════════════════════════════════════════════════════════════
           CUSTOMER INFO DISPLAY
           ═══════════════════════════════════════════════════════════════ */
        .customer-info {
            display: none;
            padding: 16px 20px;
            background: var(--gray-50);
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            border: 1px solid var(--gray-200);
        }

        .customer-info.show { display: block; }

        .customer-info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .customer-info-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--gray-800);
        }

        .customer-edit-btn {
            color: var(--primary);
            font-size: 0.85rem;
            cursor: pointer;
            font-weight: 600;
        }

        .customer-info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 0;
            font-size: 0.9rem;
            color: var(--gray-600);
        }

        .customer-info-row i {
            color: var(--gray-400);
            width: 16px;
        }

        /* ═══════════════════════════════════════════════════════════════
           MOBILE STICKY FOOTER
           ═══════════════════════════════════════════════════════════════ */
        @media (max-width: 960px) {
            .summary {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 100;
                padding: 0;
            }

            .summary-card {
                border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            }

            .summary-body {
                padding: 16px 20px;
            }

            .summary-row {
                padding: 8px 0;
            }

            .benefits,
            .guarantee,
            .social-proof,
            .trust-row,
            .features-list {
                display: none;
            }
        }

        /* ═══════════════════════════════════════════════════════════════
           ORDER BUMP
           ═══════════════════════════════════════════════════════════════ */
        .order-bump {
            margin-top: 20px;
            padding: 20px;
            border: 2px solid var(--primary);
            border-radius: var(--radius);
            background: var(--primary-light);
            position: relative;
        }

        .order-bump-check {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 28px;
            height: 28px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .order-bump-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .order-bump-icon {
            width: 56px;
            height: 56px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .order-bump-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .order-bump-text h4 {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 4px;
        }

        .order-bump-text p {
            font-size: 0.85rem;
            color: var(--gray-600);
        }

        .order-bump-price {
            margin-left: auto;
            text-align: right;
        }

        .order-bump-price .old {
            text-decoration: line-through;
            color: var(--gray-400);
            font-size: 0.85rem;
        }

        .order-bump-price .new {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <!-- Urgency Banner -->
    <div class="urgency-banner">
        <i class="fas fa-fire"></i>
        <span>{{ __('checkout_new.urgency_text') }}</span>
        <span class="urgency-timer" id="timer">15:00</span>
        <span class="urgency-spots">{{ __('checkout_new.urgency_slots') }}</span>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="logo-text">
            <i class="fas fa-church"></i> Basiléia
        </div>
        <div class="header-right">
            <div class="secure-badge">
                <i class="fas fa-lock"></i>
                {{ __('checkout_new.secure_checkout') }}
            </div>
            <div class="lang-selector">
                <button class="lang-btn" onclick="toggleLangDropdown()">
                    <span class="flag">{{ $currentLanguage['flag'] }}</span>
                    <span>{{ $currentLanguage['code'] }}</span>
                    <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                </button>
                <div class="lang-dropdown" id="lang-dropdown">
                    <div class="lang-search">
                        <input type="text" id="lang-search" placeholder="{{ __('checkout_new.search_language') }}" oninput="filterLanguages(this.value)">
                    </div>
                    <div class="lang-list" id="lang-list">
                        @foreach($availableLanguages as $lang)
                        <div class="lang-item {{ $lang['code'] === $language ? 'selected' : '' }}"
                             onclick="changeLanguage('{{ $lang['code'] }}', '{{ $lang['currency'] }}')"
                             data-search="{{ strtolower($lang['name'] . ' ' . $lang['native_name'] . ' ' . $lang['country_code'] . ' ' . $lang['currency']) }}">
                            <span class="flag">{{ $lang['flag'] }}</span>
                            <div class="info">
                                <div class="name">{{ $lang['native_name'] }}</div>
                                <div class="currency">{{ $lang['currency'] }} • {{ $lang['name'] }}</div>
                            </div>
                            <span class="check"><i class="fas fa-check-circle"></i></span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <!-- Left Column: Form -->
        <div class="form-card">
            <!-- Progress Steps -->
            <div class="progress-bar">
                <div class="progress-step active" id="progress-step-1">
                    <span class="step-circle">1</span>
                    <span class="step-text">{{ __('checkout_new.step_your_data') }}</span>
                </div>
                <div class="progress-step" id="progress-step-2">
                    <span class="step-circle">2</span>
                    <span class="step-text">{{ __('checkout_new.step_payment') }}</span>
                </div>
            </div>

            <!-- Step 1: Customer Data -->
            <div class="panel active" id="panel-step-1">
                <h2 class="panel-title">{{ __('checkout_new.step1_title') }}</h2>
                <p class="panel-subtitle">{{ __('checkout_new.step1_subtitle') }}</p>

                <form id="form-step-1">
                    <div class="field">
                        <label class="field-label">{{ __('checkout_new.full_name') }} *</label>
                        <input type="text" class="field-input" id="customer-name" placeholder="{{ __('checkout_new.full_name_placeholder') }}" required autocomplete="name">
                    </div>

                    <div class="field">
                        <label class="field-label">{{ __('checkout_new.email') }} *</label>
                        <input type="email" class="field-input" id="customer-email" placeholder="{{ __('checkout_new.email_placeholder') }}" required autocomplete="email">
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label class="field-label">{{ __('checkout_new.phone') }}</label>
                            <input type="tel" class="field-input" id="customer-phone" placeholder="{{ $currency === 'BRL' ? '(11) 99999-9999' : '+1 234 567 8900' }}" autocomplete="tel">
                        </div>
                        <div class="field">
                            <label class="field-label">{{ __('checkout_new.document') }}</label>
                            <input type="text" class="field-input" id="customer-document" placeholder="{{ $currency === 'BRL' ? '000.000.000-00' : __('checkout_new.document_placeholder') }}" autocomplete="off">
                            <p class="field-hint" id="doc-hint">{{ __('checkout_new.document_hint') }}</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="btn-continue">
                        <span class="btn-text">{{ __('checkout_new.continue_to_payment') }}</span>
                        <span class="spinner"></span>
                    </button>
                </form>

                <div class="security-note">
                    <i class="fas fa-shield-halved"></i>
                    {{ __('checkout_new.security_message') }}
                </div>
            </div>

            <!-- Step 2: Payment -->
            <div class="panel" id="panel-step-2">
                <!-- Customer Info Summary -->
                <div class="customer-info show" id="customer-summary">
                    <div class="customer-info-header">
                        <span class="customer-info-title"><i class="fas fa-user-circle"></i> {{ __('checkout_new.ordering_as') }}</span>
                        <span class="customer-edit-btn" onclick="goToStep(1)">
                            <i class="fas fa-pencil"></i> {{ __('checkout_new.edit') }}
                        </span>
                    </div>
                    <div class="customer-info-row">
                        <i class="fas fa-user"></i>
                        <span id="summary-name"></span>
                    </div>
                    <div class="customer-info-row">
                        <i class="fas fa-envelope"></i>
                        <span id="summary-email"></span>
                    </div>
                </div>

                <h2 class="panel-title">{{ __('checkout_new.payment_title') }}</h2>
                <p class="panel-subtitle">{{ __('checkout_new.payment_subtitle') }}</p>

                <!-- Payment Methods -->
                <div class="payment-methods" id="payment-methods">
                    @if(in_array('pix', $paymentMethods))
                    <div class="pay-tab active" data-method="pix" onclick="selectPayment('pix')">
                        <i class="fas fa-qrcode"></i>
                        <span>PIX</span>
                    </div>
                    @endif
                    @if(in_array('cartao', $paymentMethods))
                    <div class="pay-tab {{ !in_array('pix', $paymentMethods) ? 'active' : '' }}" data-method="cartao" onclick="selectPayment('cartao')">
                        <i class="fas fa-credit-card"></i>
                        <span>{{ __('checkout_new.card') }}</span>
                    </div>
                    @endif
                    @if(in_array('boleto', $paymentMethods))
                    <div class="pay-tab" data-method="boleto" onclick="selectPayment('boleto')">
                        <i class="fas fa-barcode"></i>
                        <span>{{ __('checkout_new.boleto') }}</span>
                    </div>
                    @endif
                </div>

                <!-- PIX Panel -->
                @if(in_array('pix', $paymentMethods))
                <div class="payment-panel active" id="panel-pix">
                    <div class="pix-display">
                        <div class="pix-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="pix-price" id="pix-price">{{ $pricing['formatted']['final_price'] }}</div>
                        <div class="pix-badge">
                            <i class="fas fa-percentage"></i> {{ __('checkout_new.pix_discount') }}
                        </div>
                        <p class="pix-desc">{{ __('checkout_new.pix_info') }}</p>
                    </div>
                </div>
                @endif

                <!-- Card Panel -->
                @if(in_array('cartao', $paymentMethods))
                <div class="payment-panel {{ !in_array('pix', $paymentMethods) ? 'active' : '' }}" id="panel-cartao">
                    <div class="card-form active">
                        <div class="card-brands">
                            <i class="fab fa-cc-visa" style="color: #1a1f71;"></i>
                            <i class="fab fa-cc-mastercard" style="color: #eb001b;"></i>
                            <i class="fab fa-cc-amex" style="color: #006fcf;"></i>
                        </div>

                        <div class="field">
                            <label class="field-label">{{ __('checkout_new.card_number') }}</label>
                            <input type="text" class="field-input" id="card-number" placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>

                        <div class="field">
                            <label class="field-label">{{ __('checkout_new.card_name') }}</label>
                            <input type="text" class="field-input" id="card-name" placeholder="{{ __('checkout_new.card_name_placeholder') }}">
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label class="field-label">{{ __('checkout_new.card_expiry') }}</label>
                                <input type="text" class="field-input" id="card-expiry" placeholder="MM/AA" maxlength="5">
                            </div>
                            <div class="field">
                                <label class="field-label">CVV</label>
                                <input type="text" class="field-input" id="card-cvv" placeholder="123" maxlength="4">
                            </div>
                        </div>

                        <!-- Installments -->
                        @if(isset($pricing['installments']) && count($pricing['installments']['options']) > 1)
                        <div class="installments-grid" id="installments-grid">
                            @foreach($pricing['installments']['options'] as $inst)
                            <div class="installment-option {{ $inst['number'] === 1 ? 'active' : '' }}" data-installments="{{ $inst['number'] }}" onclick="selectInstallments(this, {{ $inst['number'] }})">
                                <div>
                                    <span class="label">{{ $inst['number'] }}x {{ $inst['formatted'] }}</span>
                                    <span class="interest">{{ $inst['has_interest'] ? __('checkout_new.with_interest') : __('checkout_new.interest_free') }}</span>
                                </div>
                                <span class="value">{{ $inst['formatted_total'] }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Boleto Panel -->
                @if(in_array('boleto', $paymentMethods))
                <div class="payment-panel" id="panel-boleto">
                    <div class="boleto-display">
                        <i class="fas fa-barcode"></i>
                        <p style="color: var(--gray-600); margin-bottom: 8px;">{{ __('checkout_new.boleto_info') }}</p>
                        <p style="color: var(--gray-400); font-size: 0.85rem;">{{ __('checkout_new.boleto_due') }}</p>
                    </div>
                </div>
                @endif

                <!-- Coupon -->
                <div class="coupon-row">
                    <input type="text" id="coupon-input" placeholder="{{ __('checkout_new.coupon_placeholder') }}">
                    <button type="button" class="coupon-btn" onclick="validateCoupon()">{{ __('checkout_new.apply_coupon') }}</button>
                </div>
                <div class="coupon-success" id="coupon-success">
                    <i class="fas fa-check-circle"></i>
                    <span id="coupon-success-text"></span>
                </div>
                <div class="coupon-error" id="coupon-error"></div>

                <!-- Order Bump Example (hidden by default) -->
                @if(isset($pricing['order_bumps']) && count($pricing['order_bumps']['items']) > 0)
                <div class="order-bump" id="order-bump">
                    <div class="order-bump-check" onclick="toggleOrderBump()">
                        <i class="fas fa-check" id="bump-check-icon" style="display: none;"></i>
                    </div>
                    <div class="order-bump-content">
                        <div class="order-bump-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <div class="order-bump-text">
                            <h4>{{ __('checkout_new.order_bump_title') }}</h4>
                            <p>{{ __('checkout_new.order_bump_desc') }}</p>
                        </div>
                        <div class="order-bump-price">
                            <span class="old">{{ $pricing['formatted']['final_price'] }}</span>
                            <span class="new">{{ __('checkout_new.order_bump_price') }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <button type="button" class="btn btn-primary" id="btn-pay" onclick="processPayment()">
                    <span class="btn-text"><i class="fas fa-lock"></i> {{ __('checkout_new.pay_button') }}</span>
                    <span class="spinner"></span>
                </button>

                <button type="button" class="btn btn-back" onclick="goToStep(1)">
                    <i class="fas fa-arrow-left"></i> {{ __('checkout_new.back') }}
                </button>

                <div class="security-note">
                    <i class="fas fa-shield-halved"></i>
                    {{ __('checkout_new.asaas_secure') }}
                </div>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <aside class="summary">
            <div class="summary-card">
                <div class="summary-header">
                    <div class="summary-label">{{ __('checkout_new.order_summary') }}</div>
                    <div class="summary-plan">{{ $pricing['offer']['name'] }}</div>
                </div>

                <div class="summary-body">
                    <!-- Currency Badge -->
                    <div class="currency-badge">
                        <i class="fas fa-globe"></i>
                        {{ $currency }}
                    </div>

                    <div class="summary-row">
                        <span class="label">{{ __('checkout_new.plan') }}</span>
                        <span class="value">{{ $pricing['offer']['name'] }}</span>
                    </div>

                    <div class="summary-row original">
                        <span class="label">{{ __('checkout_new.original_price') }}</span>
                        <span class="value">{{ $pricing['formatted']['original_price'] }}</span>
                    </div>

                    @if(($pricing['discounts']['total_discount'] ?? 0) > 0)
                    <div class="summary-row">
                        <span class="label">{{ __('checkout_new.discount') }}</span>
                        <span class="discount-badge">-{{ $pricing['discounts']['formatted_discount'] ?? '' }}</span>
                    </div>
                    @endif

                    <div class="summary-row total">
                        <span class="label">{{ __('checkout_new.total') }}</span>
                        <span class="value" id="summary-total">{{ $pricing['formatted']['final_price'] }}</span>
                    </div>

                    <!-- Benefits -->
                    <div class="benefits">
                        @foreach($pricing['features'] as $feature)
                        <div class="benefit">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Guarantee -->
                    <div class="guarantee">
                        <div class="guarantee-icon">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div class="guarantee-text">
                            <h4>{{ __('checkout_new.guarantee_title', ['days' => $pricing['guarantee'] ?? '7']) }}</h4>
                            <p>{{ __('checkout_new.guarantee_text') }}</p>
                        </div>
                    </div>

                    <!-- Social Proof -->
                    <div class="social-proof">
                        <div class="social-proof-title">{{ __('checkout_new.social_title') }}</div>
                        <div class="social-proof-number">3.421+</div>
                        <div class="social-proof-stars">
                            ★★★★★ <span>4.9/5</span>
                        </div>
                    </div>

                    <!-- Trust Badges -->
                    <div class="trust-row">
                        <div class="trust-item">
                            <i class="fas fa-lock"></i>
                            SSL
                        </div>
                        <div class="trust-item">
                            <i class="fas fa-shield-halved"></i>
                            {{ __('checkout_new.trust_secure') }}
                        </div>
                        <div class="trust-item">
                            <i class="fas fa-check-circle"></i>
                            {{ __('checkout_new.trust_guarantee') }}
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </main>

    <script>
        // ═══════════════════════════════════════════════════════════════
        // CONFIGURATION
        // ═══════════════════════════════════════════════════════════════
        const CONFIG = {
            sessionToken: '{{ $session_token }}',
            currency: '{{ $currency }}',
            language: '{{ $language }}',
            allowedPayments: @json($paymentMethods),
            pricing: @json($pricing)
        };

        let currentStep = 1;
        let selectedPayment = CONFIG.allowedPayments.includes('pix') ? 'pix' : 'cartao';
        let customerData = {};
        let selectedInstallments = 1;

        // ═══════════════════════════════════════════════════════════════
        // URGENCY TIMER
        // ═══════════════════════════════════════════════════════════════
        let timeLeft = 15 * 60;
        const timerEl = document.getElementById('timer');

        function updateTimer() {
            const min = Math.floor(timeLeft / 60);
            const sec = timeLeft % 60;
            timerEl.textContent = `${min.toString().padStart(2, '0')}:${sec.toString().padStart(2, '0')}`;
            if (timeLeft <= 0) {
                timerEl.textContent = 'EXPIRED';
                timerEl.style.background = 'var(--danger)';
            }
            timeLeft--;
        }
        setInterval(updateTimer, 1000);

        // ═══════════════════════════════════════════════════════════════
        // INPUT MASKS
        // ═══════════════════════════════════════════════════════════════
        document.getElementById('customer-phone').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (CONFIG.currency === 'BRL') {
                if (v.length > 11) v = v.slice(0, 11);
                if (v.length > 7) v = `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
                else if (v.length > 2) v = `(${v.slice(0,2)}) ${v.slice(2)}`;
                else if (v.length > 0) v = `(${v}`;
            }
            e.target.value = v;
        });

        document.getElementById('customer-document').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (CONFIG.currency === 'BRL') {
                if (v.length > 11) v = v.slice(0, 11);
                if (v.length > 9) v = `${v.slice(0,3)}.${v.slice(3,6)}.${v.slice(6,9)}-${v.slice(9)}`;
                else if (v.length > 6) v = `${v.slice(0,3)}.${v.slice(3,6)}.${v.slice(6)}`;
                else if (v.length > 3) v = `${v.slice(0,3)}.${v.slice(3)}`;
            }
            e.target.value = v;
        });

        document.getElementById('card-number').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 16);
            v = v.replace(/(.{4})/g, '$1 ').trim();
            e.target.value = v;
        });

        document.getElementById('card-expiry').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 4);
            if (v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
            e.target.value = v;
        });

        // ═══════════════════════════════════════════════════════════════
        // STEP NAVIGATION
        // ═══════════════════════════════════════════════════════════════
        function goToStep(step) {
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.progress-step').forEach(s => {
                s.classList.remove('active', 'done');
            });

            for (let i = 1; i < step; i++) {
                document.getElementById(`progress-step-${i}`).classList.add('done');
            }
            document.getElementById(`progress-step-${step}`).classList.add('active');
            document.getElementById(`panel-step-${step}`).classList.add('active');
            currentStep = step;

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // ═══════════════════════════════════════════════════════════════
        // LANGUAGE SELECTOR
        // ═══════════════════════════════════════════════════════════════
        function toggleLangDropdown() {
            document.getElementById('lang-dropdown').classList.toggle('active');
        }

        function filterLanguages(query) {
            const items = document.querySelectorAll('.lang-item');
            query = query.toLowerCase();
            items.forEach(item => {
                const search = item.getAttribute('data-search');
                item.style.display = search.includes(query) ? 'flex' : 'none';
            });
        }

        function changeLanguage(langCode, currency) {
            const url = new URL(window.location.href);
            url.searchParams.set('lang', langCode);
            url.searchParams.set('moeda', currency);
            window.location.href = url.toString();
        }

        document.addEventListener('click', function(e) {
            if (!document.querySelector('.lang-selector').contains(e.target)) {
                document.getElementById('lang-dropdown').classList.remove('active');
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // PAYMENT SELECTION
        // ═══════════════════════════════════════════════════════════════
        function selectPayment(method) {
            selectedPayment = method;
            document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.payment-panel').forEach(p => p.classList.remove('active'));
            document.querySelector(`.pay-tab[data-method="${method}"]`).classList.add('active');
            document.getElementById(`panel-${method}`).classList.add('active');
        }

        function selectInstallments(el, number) {
            selectedInstallments = number;
            document.querySelectorAll('.installment-option').forEach(o => o.classList.remove('active'));
            el.classList.add('active');
        }

        // ═══════════════════════════════════════════════════════════════
        // ORDER BUMP
        // ═══════════════════════════════════════════════════════════════
        let orderBumpActive = false;
        function toggleOrderBump() {
            orderBumpActive = !orderBumpActive;
            document.getElementById('bump-check-icon').style.display = orderBumpActive ? 'block' : 'none';
        }

        // ═══════════════════════════════════════════════════════════════
        // COUPON
        // ═══════════════════════════════════════════════════════════════
        async function validateCoupon() {
            const code = document.getElementById('coupon-input').value.trim();
            if (!code) return;

            const successEl = document.getElementById('coupon-success');
            const errorEl = document.getElementById('coupon-error');
            successEl.classList.remove('show');
            errorEl.classList.remove('show');

            try {
                const res = await fetch('/co/validate-coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_token: CONFIG.sessionToken,
                        coupon_code: code
                    })
                });

                const data = await res.json();

                if (data.valid) {
                    successEl.classList.add('show');
                    document.getElementById('coupon-success-text').textContent = `-${data.formatted_discount}`;
                } else {
                    errorEl.classList.add('show');
                    errorEl.textContent = data.error || '{{ __('checkout.coupon_invalid') }}';
                }
            } catch (e) {
                errorEl.classList.add('show');
                errorEl.textContent = '{{ __('checkout.coupon_error') }}';
            }
        }

        // ═══════════════════════════════════════════════════════════════
        // FORM SUBMISSION - STEP 1
        // ═══════════════════════════════════════════════════════════════
        document.getElementById('form-step-1').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-continue');
            btn.classList.add('loading');
            btn.disabled = true;

            customerData = {
                name: document.getElementById('customer-name').value.trim(),
                email: document.getElementById('customer-email').value.trim(),
                phone: document.getElementById('customer-phone').value.trim(),
                document: document.getElementById('customer-document').value.trim()
            };

            try {
                const res = await fetch('/co/identify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_token: CONFIG.sessionToken,
                        ...customerData
                    })
                });

                const data = await res.json();

                if (data.success) {
                    // Update customer summary
                    document.getElementById('summary-name').textContent = customerData.name;
                    document.getElementById('summary-email').textContent = customerData.email;

                    goToStep(2);
                } else {
                    alert(data.message || '{{ __('checkout.identify_error') }}');
                }
            } catch (error) {
                alert('{{ __('checkout.connection_error') }}');
            } finally {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // PAYMENT PROCESSING
        // ═══════════════════════════════════════════════════════════════
        async function processPayment() {
            const btn = document.getElementById('btn-pay');
            btn.classList.add('loading');
            btn.disabled = true;

            const payload = {
                session_token: CONFIG.sessionToken,
                payment_method: selectedPayment,
                installments: selectedInstallments,
                order_bump: orderBumpActive
            };

            if (selectedPayment === 'cartao') {
                payload.card = {
                    number: document.getElementById('card-number').value.replace(/\s/g, ''),
                    name: document.getElementById('card-name').value,
                    expiry: document.getElementById('card-expiry').value,
                    cvv: document.getElementById('card-cvv').value
                };
            }

            try {
                const res = await fetch('/co/pay', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    if (selectedPayment === 'pix' && data.pix_copy_paste) {
                        showPixModal(data);
                    } else if (data.bank_slip_url) {
                        window.open(data.bank_slip_url, '_blank');
                        window.location.href = `/co/success/${data.order_number}`;
                    } else if (data.invoice_url) {
                        window.location.href = data.invoice_url;
                    } else {
                        window.location.href = `/co/success/${data.order_number}`;
                    }
                } else {
                    alert(data.message || '{{ __('checkout.payment_error') }}');
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            } catch (error) {
                alert('{{ __('checkout.connection_error') }}');
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        }

        // ═══════════════════════════════════════════════════════════════
        // PIX MODAL
        // ═══════════════════════════════════════════════════════════════
        function showPixModal(data) {
            const modal = document.createElement('div');
            modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:9999;';
            modal.innerHTML = `
                <div style="background:white;padding:40px;border-radius:20px;max-width:420px;width:90%;text-align:center;">
                    <div style="width:80px;height:80px;background:#D1FAE5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                        <i class="fas fa-check" style="font-size:2rem;color:#10B981;"></i>
                    </div>
                    <h3 style="font-size:1.5rem;margin-bottom:8px;color:#111827;">{{ __('checkout_new.pix_generated') }}</h3>
                    <p style="color:#6B7280;margin-bottom:20px;">{{ __('checkout_new.pix_copy_info') }}</p>
                    <div style="background:#F3F4F6;padding:16px;border-radius:12px;word-break:break-all;font-family:monospace;font-size:0.85rem;max-height:120px;overflow-y:auto;margin-bottom:20px;">
                        ${data.pix_copy_paste || '{{ __('checkout_new.pix_waiting') }}'}
                    </div>
                    <button onclick="navigator.clipboard.writeText('${data.pix_copy_paste}').then(()=>this.innerHTML='<i class=fas fa-check></i> {{ __('checkout_new.copied') }}')" style="background:var(--primary);color:white;padding:16px 24px;border:none;border-radius:12px;font-weight:700;cursor:pointer;width:100%;margin-bottom:12px;font-size:1rem;">
                        <i class="fas fa-copy"></i> {{ __('checkout_new.copy_pix_code') }}
                    </button>
                    <button onclick="window.location.href='/co/success/${data.order_number}?lang={{ $language }}'" style="background:var(--gray-100);color:var(--gray-700);padding:16px 24px;border:none;border-radius:12px;font-weight:600;cursor:pointer;width:100%;font-size:0.95rem;">
                        {{ __('checkout_new.go_to_success') }}
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
        }
    </script>
</body>
</html>
