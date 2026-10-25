
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temairazu Hotel | Your stay in Japan</title>
    <style>
        :root {
            --ink: #202a35;
            --muted: #6f7882;
            --paper: #f7f4ee;
            --white: #ffffff;
            --wine: #782f3b;
            --wine-dark: #55202a;
            --gold: #b18a55;
            --line: #e6dfd5;
            --sage: #e8eee8;
            --shadow: 0 18px 48px rgba(44, 35, 31, .08);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            color: var(--ink);
            background: var(--paper);
            font-family: Inter, "Noto Sans", "Noto Sans JP", "Yu Gothic", Meiryo, sans-serif;
            line-height: 1.6;
        }
        button, input, select, textarea { font: inherit; }
        button {
            border: 0; border-radius: 4px; padding: 12px 18px;
            background: var(--wine); color: white; font-weight: 650;
            cursor: pointer; transition: background .2s, transform .2s, box-shadow .2s;
        }
        button:hover { background: var(--wine-dark); box-shadow: 0 6px 18px #55202a22; }
        button:active { transform: translateY(1px); }
        button:disabled { opacity: .55; cursor: wait; }
        button.secondary { background: var(--ink); }
        button.light { background: transparent; color: var(--ink); border: 1px solid var(--line); }
        button.light:hover { background: #f0ebe4; }
        .site-header {
            background: rgba(255,255,255,.96); border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 20; backdrop-filter: blur(12px);
        }
        .header-inner {
            max-width: 1240px; margin: auto; padding: 17px 28px;
            display: flex; justify-content: space-between; align-items: center; gap: 22px;
        }
        .brand { display: flex; align-items: center; gap: 13px; min-width: 220px; }
        .brand-mark {
            width: 42px; height: 42px; display: grid; place-items: center;
            border: 1px solid var(--gold); color: var(--wine); font-size: 22px;
            font-family: Georgia, serif; border-radius: 50%;
        }
        .brand h1 { margin: 0; font-family: Georgia, "Noto Serif JP", serif; font-size: 22px; letter-spacing: .02em; }
        .brand p { margin: 1px 0 0; color: var(--muted); font-size: 11px; letter-spacing: .16em; text-transform: uppercase; }
        nav { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        nav button { margin: 0; padding: 9px 13px; font-size: 13px; }
        .language-toggle { border: 1px solid var(--line) !important; background: white !important; color: var(--wine) !important; }
        .hero {
            position: relative; overflow: hidden; min-height: 300px;
            background: linear-gradient(105deg, rgba(31,35,39,.83), rgba(31,35,39,.36)),
                linear-gradient(135deg, #4c5c59, #a88b70 50%, #e0c8a6);
            color: white; display: flex; align-items: center;
        }
        .hero::after {
            content: "旅"; position: absolute; right: 9%; top: -52px;
            font-family: "Noto Serif JP", Georgia, serif; font-size: 340px;
            color: rgba(255,255,255,.07); line-height: 1; pointer-events: none;
        }
        .hero-inner { max-width: 1240px; width: 100%; margin: auto; padding: 58px 28px 66px; position: relative; z-index: 1; }
        .eyebrow { color: #e6c99a; letter-spacing: .2em; text-transform: uppercase; font-size: 11px; font-weight: 700; }
        .hero h2 { font-family: Georgia, "Noto Serif JP", serif; font-size: clamp(34px, 5vw, 56px); line-height: 1.15; max-width: 660px; margin: 12px 0 14px; font-weight: 500; }
        .hero p { max-width: 560px; margin: 0; color: #f2eee8; font-size: 16px; }
        main { max-width: 1240px; margin: -28px auto 70px; padding: 0 28px; position: relative; z-index: 2; }
        .panel {
            background: var(--white); padding: clamp(22px, 3vw, 34px);
            margin-bottom: 24px; border: 1px solid rgba(230,223,213,.9);
            border-radius: 8px; box-shadow: var(--shadow);
        }
        h2 { margin: 0 0 9px; font-family: Georgia, "Noto Serif JP", serif; font-size: 27px; font-weight: 500; }
        h3 { font-family: Georgia, "Noto Serif JP", serif; }
        .muted { color: var(--muted); }
        .section-kicker { color: var(--gold); letter-spacing: .17em; text-transform: uppercase; font-size: 10px; font-weight: 800; margin-bottom: 7px; }
        .search-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px; margin-top: 24px; }
        label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 13px; }
        input, select, textarea {
            width: 100%; padding: 13px 14px; border: 1px solid #d8d3cb;
            border-radius: 4px; background: #fffdfa; color: var(--ink); font-size: 14px;
            outline: none; transition: border .2s, box-shadow .2s;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--gold); box-shadow: 0 0 0 3px #b18a551c; }
        #searchButton { margin-top: 22px; min-width: 220px; }
        .message { margin-top: 15px; line-height: 1.6; overflow-wrap: anywhere; font-size: 14px; }
        .error { color: #b42318; } .success { color: #18734b; }
        .hidden { display: none !important; }
        .room-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; }
        .room, .booking {
            border: 1px solid var(--line); border-radius: 6px; padding: 23px;
            margin-top: 16px; background: linear-gradient(145deg, #fff, #fdfbf7);
        }
        .room { position: relative; overflow: hidden; }
        .room::before { content: ""; display: block; height: 4px; width: 44px; background: var(--gold); margin-bottom: 16px; }
        .room h3, .booking h3 { margin: 0 0 10px; font-size: 22px; }
        .price { font-size: 25px; font-weight: 750; color: var(--wine); margin: 12px 0; letter-spacing: -.02em; }
        .room button { margin-top: 10px; width: 100%; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 3px; background: #f6eddb; color: #79551f; font-size: 11px; font-weight: 800; letter-spacing: .06em; }
        .form-actions { display: flex; gap: 10px; flex-wrap: wrap; margin: 20px 0; }
        .form-actions button { margin: 0; }
        #selectedStay { border: 1px solid var(--line); background: #fbf8f2; padding: 20px; border-radius: 5px; margin: 18px 0 24px; }
        #bookingForm button { margin-top: 18px; }
        .booking { border-left: 4px solid var(--gold); }
        footer { text-align: center; padding: 35px 20px; color: #d9d2c8; background: #252a2e; font-size: 12px; letter-spacing: .08em; }
        footer span { display: block; margin-top: 5px; color: #a9a49c; letter-spacing: .02em; }
        @media (max-width: 760px) {
            .header-inner { padding: 14px 18px; align-items: flex-start; flex-direction: column; }
            nav { justify-content: flex-start; }
            .hero-inner { padding: 46px 20px 56px; }
            main { padding: 0 14px; margin-top: -18px; }
            .search-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .panel { padding: 22px 18px; }
        }
        @media (max-width: 430px) {
            .search-grid { grid-template-columns: 1fr; }
            nav button { flex: 1; }
            #searchButton { width: 100%; }
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="header-inner">
    <div class="brand">
        <div class="brand-mark" aria-hidden="true">手</div>
        <div>
            <h1>Temairazu Hotel</h1>
            <p>Stay in Japan, beautifully</p>
        </div>
    </div>
    <nav>
        <button type="button" id="languageToggle" class="language-toggle" aria-label="Switch language">日本語</button>
        <button type="button" id="accountButton" class="secondary">Sign in / Register</button>
        <button type="button" id="myBookingsButton" class="light hidden">My Bookings</button>
        <button type="button" id="logoutButton" class="light hidden">Sign out</button>
    </nav>
    </div>
</header>

<section class="hero">
    <div class="hero-inner">
        <div class="eyebrow">Hospitality · Japan</div>
        <h2 id="heroTitle">A thoughtful stay, inspired by Japan.</h2>
        <p id="heroSubtitle">Discover a calmer kind of comfort, with attentive service and a stay designed around you.</p>
    </div>
</section>

<main>
    <section class="panel">
        <div class="section-kicker">Reservation</div>
        <h2>Find your stay</h2>
        <p class="muted">Choose your dates and discover available rooms at our Tokyo hotel.</p>

        <form id="searchForm">
            <div class="search-grid">
                <div>
                    <label for="checkIn">Check-in</label>
                    <input type="date" id="checkIn" required>
                </div>
                <div>
                    <label for="checkOut">Check-out</label>
                    <input type="date" id="checkOut" required>
                </div>
                <div>
                    <label for="adults">Adults</label>
                    <select id="adults">
                        <option value="1">1 adult</option>
                        <option value="2" selected>2 adults</option>
                        <option value="3">3 adults</option>
                        <option value="4">4 adults</option>
                    </select>
                </div>
                <div>
                    <label for="children">Children</label>
                    <select id="children">
                        <option value="0" selected>0 children</option>
                        <option value="1">1 child</option>
                        <option value="2">2 children</option>
                        <option value="3">3 children</option>
                    </select>
                </div>
            </div>
            <button type="submit" id="searchButton">Search availability</button>
        </form>
        <div id="searchMessage" class="message" role="status"></div>
    </section>

    <section class="panel hidden" id="resultsPanel">
        <h2>Available rooms</h2>
        <p class="muted" id="staySummary"></p>
        <div id="roomResults"></div>
    </section>

    <section class="panel hidden" id="accountPanel">
        <h2 id="accountTitle">Customer account</h2>
        <p class="muted" id="accountIntro">Sign in or create an account to continue with your reservation.</p>

        <div class="form-actions">
            <button type="button" id="showLogin" class="secondary">Sign in</button>
            <button type="button" id="showRegister" class="light">Create account</button>
        </div>

        <form id="loginForm" class="hidden">
            <div class="search-grid">
                <div>
                    <label for="loginEmail">Email</label>
                    <input id="loginEmail" type="email" autocomplete="email" required>
                </div>
                <div>
                    <label for="loginPassword">Password</label>
                    <input id="loginPassword" type="password" autocomplete="current-password" required>
                </div>
            </div>
            <button type="submit">Sign in</button>
        </form>

        <form id="registerForm" class="hidden">
            <div class="search-grid">
                <div>
                    <label for="firstName">First name</label>
                    <input id="firstName" maxlength="100" autocomplete="given-name" required>
                </div>
                <div>
                    <label for="lastName">Last name</label>
                    <input id="lastName" maxlength="100" autocomplete="family-name" required>
                </div>
                <div>
                    <label for="registerEmail">Email</label>
                    <input id="registerEmail" type="email" maxlength="255" autocomplete="email" required>
                </div>
                <div>
                    <label for="phone">Phone</label>
                    <input id="phone" maxlength="30" autocomplete="tel" required>
                </div>
                <div>
                    <label for="registerPassword">Password (minimum 8 characters)</label>
                    <input id="registerPassword" type="password" minlength="8" autocomplete="new-password" required>
                </div>
                <div>
                    <label for="confirmPassword">Confirm password</label>
                    <input id="confirmPassword" type="password" minlength="8" autocomplete="new-password" required>
                </div>
            </div>
            <button type="submit">Create account</button>
        </form>

        <div id="accountMessage" class="message" role="status"></div>
    </section>

    <section class="panel hidden" id="confirmationPanel">
        <h2>Complete your reservation</h2>
        <div id="selectedStay"></div>

        <form id="bookingForm">
            <div>
                <label for="specialRequests">Special requests (optional)</label>
                <textarea id="specialRequests" rows="3" maxlength="1000" placeholder="Let us know if you have any requests."></textarea>
            </div>
            <button type="submit" id="confirmBookingButton">Confirm booking</button>
        </form>
        <div id="bookingMessage" class="message" role="status"></div>
    </section>

    <section class="panel hidden" id="myBookingsPanel">
        <h2>My Bookings</h2>
        <p class="muted">Your reservations associated with your customer account.</p>
        <button type="button" id="refreshBookingsButton" class="secondary">Refresh bookings</button>
        <div id="bookingsMessage" class="message" role="status"></div>
        <div id="bookingsList"></div>
    </section>
</main>

<footer>手間いらずホテル · TEMAIRAZU HOTEL<span>Tokyo, Japan · Thoughtful hospitality, every day</span></footer>

<script>
(() => {
    const $ = id => document.getElementById(id);
    const API = '/api/v1/customer';
    const TOKEN_KEY = 'hotelia_customer_token';
    const SELECTION_KEY = 'hotelia_booking_selection';

    let token = sessionStorage.getItem(TOKEN_KEY);
    let selection = readSelection();

    function readSelection() {
        try {
            return JSON.parse(sessionStorage.getItem(SELECTION_KEY) || 'null');
        } catch {
            return null;
        }
    }

    function showMessage(element, message, type = '') {
        element.className = 'message' + (type ? ' ' + type : '');
        element.textContent = message || '';
    }

    function hide(element) {
        element.classList.add('hidden');
    }

    function show(element) {
        element.classList.remove('hidden');
    }

    function localDate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function money(amount) {
        return '¥' + Number(amount || 0).toLocaleString('ja-JP', {
            maximumFractionDigits: 0
        });
    }

    function safeText(value) {
        return String(value ?? '');
    }

    async function api(path, options = {}) {
        const headers = {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(options.headers || {})
        };

        const response = await fetch(API + path, {
            ...options,
            headers
        });

        const text = await response.text();
        let result = {};

        try {
            result = text ? JSON.parse(text) : {};
        } catch {
            throw new Error('The server returned an unexpected response.');
        }

        if (!response.ok || result.success === false) {
            const validationErrors = result.errors
                ? Object.values(result.errors).flat().join(' ')
                : '';

            throw new Error(validationErrors || result.message || `Request failed (${response.status}).`);
        }

        return result;
    }


    const translations = {
        "Find your stay": "ご宿泊を検索",
        "Choose your dates and discover available rooms at our Tokyo hotel.": "東京のホテルで、ご希望の日程に空室があるお部屋をお探しください。",
        "Check-in": "チェックイン",
        "Check-out": "チェックアウト",
        "Adults": "大人",
        "Children": "お子様",
        "Search availability": "空室を検索",
        "Searching...": "検索中...",
        "Available rooms": "空室一覧",
        "Sign in / Register": "ログイン／新規登録",
        "Account": "アカウント",
        "My Bookings": "予約一覧",
        "Sign out": "ログアウト",
        "Customer account": "お客様アカウント",
        "Sign in or create an account to continue with your reservation.": "予約を続けるにはログインまたは新規登録してください。",
        "Sign in": "ログイン",
        "Create account": "アカウント作成",
        "Create your account": "アカウントを作成",
        "Sign in to confirm your booking or view your reservations.": "予約の確定や予約内容の確認にはログインしてください。",
        "Create an account to manage your hotel reservations.": "ホテルの予約を管理するアカウントを作成してください。",
        "Email": "メールアドレス",
        "Password": "パスワード",
        "Password (minimum 8 characters)": "パスワード（8文字以上）",
        "Confirm password": "パスワード（確認）",
        "First name": "名",
        "Last name": "姓",
        "Phone": "電話番号",
        "Complete your reservation": "予約内容の確認",
        "Special requests (optional)": "ご要望（任意）",
        "Let us know if you have any requests.": "ご希望があればご記入ください。",
        "Confirm booking": "予約を確定",
        "Your reservations associated with your customer account.": "お客様のアカウントに登録されている予約です。",
        "Refresh bookings": "予約を更新",
        "A thoughtful stay, inspired by Japan.": "日本のおもてなしで、心ほどける滞在を。",
        "Discover a calmer kind of comfort, with attentive service and a stay designed around you.": "細やかなサービスと、あなたに寄り添う快適さをご体験ください。",
        "Reservation": "ご予約",
        "Stay in Japan, beautifully": "日本で、心地よい滞在を",
        "Hospitality · Japan": "おもてなし · 日本",
        "No rooms are available for all your selected dates.": "選択した日程では空室がありません。",
        "This is an estimate. The server calculates the final booking price.": "表示金額は概算です。最終料金はサーバー側で計算されます。",
        "You have no bookings yet.": "予約はまだありません。",
        "Loading your bookings...": "予約を読み込んでいます...",
        "Checking live room availability...": "空室状況を確認しています...",
        "Check-out must be after check-in.": "チェックアウト日はチェックイン日より後の日付を選択してください。",
        "Please sign in to confirm your booking.": "予約を確定するにはログインしてください。",
        "Please search and choose a room first.": "先に空室を検索し、お部屋を選択してください。",
        "Passwords do not match.": "パスワードが一致しません。",
        "Signing in...": "ログイン中...",
        "Creating your account...": "アカウントを作成しています...",
        "Creating your booking...": "予約を作成しています...",
        "You have signed out.": "ログアウトしました。",
        "Choose this room": "この部屋を選択",
        "adult(s)": "名",
        "child(ren)": "名",
        "night(s)": "泊",
        "room(s) available for your stay": "室が空いています",
        "Estimated total:": "概算合計：",
        " per night": "／泊",
        "Check-in: ": "チェックイン：",
        " · Check-out: ": " · チェックアウト：",
        "Room(s): ": "部屋："
    };
    let currentLanguage = 'en';
    function applyLanguage() {
        const map = currentLanguage === 'ja' ? translations : Object.fromEntries(
            Object.entries(translations).map(([en, ja]) => [ja, en])
        );
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        for (const node of nodes) {
            const value = node.nodeValue;
            const trimmed = value.trim();
            if (map[trimmed]) {
                node.nodeValue = value.replace(trimmed, map[trimmed]);
            }
        }
        const toggle = $('languageToggle');
        if (toggle) toggle.textContent = currentLanguage === 'en' ? '日本語' : 'English';
        document.documentElement.lang = currentLanguage === 'ja' ? 'ja' : 'en';
    }
    $('languageToggle').addEventListener('click', () => {
        currentLanguage = currentLanguage === 'en' ? 'ja' : 'en';
        applyLanguage();
    });

    function updateAccountUI() {
        $('accountButton').textContent = currentLanguage === 'ja' ? (token ? 'アカウント' : 'ログイン／新規登録') : (token ? 'Account' : 'Sign in / Register');
        $('logoutButton').textContent = currentLanguage === 'ja' ? 'ログアウト' : 'Sign out';
        $('myBookingsButton').textContent = currentLanguage === 'ja' ? '予約一覧' : 'My Bookings';
        $('logoutButton').classList.toggle('hidden', !token);
        $('myBookingsButton').classList.toggle('hidden', !token);
    }

    function openAccount(mode = 'login') {
        show($('accountPanel'));
        hide($('confirmationPanel'));
        hide($('myBookingsPanel'));

        $('loginForm').classList.toggle('hidden', mode !== 'login');
        $('registerForm').classList.toggle('hidden', mode !== 'register');
        $('accountTitle').textContent = mode === 'register' ? 'Create your account' : 'Customer sign in';
        $('accountIntro').textContent = mode === 'register'
            ? 'Create an account to manage your hotel reservations.'
            : 'Sign in to confirm your booking or view your reservations.';
        showMessage($('accountMessage'), '');
        $('accountPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function displaySelection() {
        if (!selection) return;

        $('selectedStay').replaceChildren();

        const heading = document.createElement('h3');
        heading.textContent = selection.roomTypeName;

        const details = document.createElement('p');
        details.textContent =
            `${selection.checkIn} to ${selection.checkOut} · ${selection.nights} night(s) · ` +
            `${selection.adults} adult(s) · ${selection.children} child(ren)`;

        const total = document.createElement('p');
        total.className = 'price';
        total.textContent = money(selection.estimatedTotal);

        const note = document.createElement('p');
        note.className = 'muted';
        note.textContent = 'This is an estimate. The server calculates the final booking price.';

        $('selectedStay').append(heading, details, total, note);
        show($('confirmationPanel'));
    }

    async function searchAvailability(event) {
        if (event) event.preventDefault();

        const start = $('checkIn').value;
        const end = $('checkOut').value;

        if (!start || !end || end <= start) {
            showMessage($('searchMessage'), 'Check-out must be after check-in.', 'error');
            return;
        }

        $('searchButton').disabled = true;
        $('searchButton').textContent = 'Searching...';
        showMessage($('searchMessage'), 'Checking live room availability...');
        hide($('resultsPanel'));
        $('roomResults').replaceChildren();

        try {
            const params = new URLSearchParams({ start_date: start, end_date: end });
            const result = await fetch(`${API}/availability?${params}`, {
                headers: { Accept: 'application/json' }
            }).then(async response => {
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Could not check room availability.');
                }
                return data;
            });

            const nights = Math.round(
                (new Date(`${end}T12:00:00`) - new Date(`${start}T12:00:00`)) / 86400000
            );

            const rooms = new Map();

            for (const day of result.data.matrix || []) {
                for (const item of day.room_types || []) {
                    if (!rooms.has(item.room_type_id)) {
                        rooms.set(item.room_type_id, {
                            id: item.room_type_id,
                            name: item.room_type_name,
                            available: Number(item.available_rooms),
                            price: Number(item.price)
                        });
                    } else {
                        const room = rooms.get(item.room_type_id);
                        room.available = Math.min(room.available, Number(item.available_rooms));
                    }
                }
            }

            const availableRooms = [...rooms.values()].filter(room => room.available > 0);
            $('staySummary').textContent = `${start} to ${end} | ${nights} night(s)`;

            if (!availableRooms.length) {
                $('roomResults').textContent = 'No rooms are available for all your selected dates.';
            } else {
                availableRooms.forEach(room => {
                    const card = document.createElement('article');
                    card.className = 'room';

                    const title = document.createElement('h3');
                    title.textContent = room.name;

                    const available = document.createElement('p');
                    available.className = 'muted';
                    available.textContent = `${room.available} room(s) available for your stay`;

                    const price = document.createElement('p');
                    price.className = 'price';
                    price.textContent = `${money(room.price)} per night`;

                    const total = document.createElement('p');
                    total.textContent = `Estimated total: ${money(room.price * nights)} for ${nights} night(s).`;

                    const choose = document.createElement('button');
                    choose.type = 'button';
                    choose.textContent = 'Choose this room';

                    choose.addEventListener('click', () => {
                        selection = {
                            hotelId: result.data.hotel_id,
                            roomTypeId: room.id,
                            roomTypeName: room.name,
                            checkIn: start,
                            checkOut: end,
                            adults: Number($('adults').value),
                            children: Number($('children').value),
                            nights,
                            nightlyPrice: room.price,
                            estimatedTotal: room.price * nights
                        };

                        sessionStorage.setItem(SELECTION_KEY, JSON.stringify(selection));
                        displaySelection();

                        if (token) {
                            showMessage($('bookingMessage'), 'Review your reservation and confirm below.', 'success');
                            $('confirmationPanel').scrollIntoView({ behavior: 'smooth' });
                        } else {
                            showMessage($('searchMessage'), 'Room selected. Sign in or create an account to continue.', 'success');
                            openAccount('login');
                        }
                    });

                    card.append(title, available, price, total, choose);
                    $('roomResults').appendChild(card);
                });
            }

            show($('resultsPanel'));
            showMessage($('searchMessage'), '');
            if (currentLanguage === 'ja') applyLanguage();
        } catch (error) {
            showMessage($('searchMessage'), error.message, 'error');
        } finally {
            $('searchButton').disabled = false;
            $('searchButton').textContent = 'Search availability';
        }
    }

    async function loadBookings() {
        if (!token) {
            openAccount('login');
            return;
        }

        show($('myBookingsPanel'));
        hide($('accountPanel'));
        hide($('confirmationPanel'));
        $('bookingsList').replaceChildren();
        showMessage($('bookingsMessage'), 'Loading your bookings...');

        try {
            const result = await api('/bookings');
            const bookings = result.data || [];

            if (!bookings.length) {
                showMessage($('bookingsMessage'), 'You have no bookings yet.');
                return;
            }

            showMessage($('bookingsMessage'), `${bookings.length} booking(s) found.`, 'success');

            bookings.forEach(booking => {
                const card = document.createElement('article');
                card.className = 'booking';

                const title = document.createElement('h3');
                title.textContent = `Booking ${safeText(booking.booking_reference)}`;

                const status = document.createElement('span');
                status.className = 'status';
                status.textContent = safeText(booking.status).replaceAll('_', ' ').toUpperCase();

                const hotel = document.createElement('p');
                hotel.textContent = safeText(booking.hotel?.name || 'Grand Hotelia');

                const dates = document.createElement('p');
                dates.textContent =
                    `Check-in: ${safeText(booking.check_in_date).slice(0, 10)} · ` +
                    `Check-out: ${safeText(booking.check_out_date).slice(0, 10)}`;

                const room = document.createElement('p');
                room.textContent = 'Room(s): ' + (booking.rooms || [])
                    .map(item => `${item.room_type?.name || 'Room'} (${item.room_number || item.id})`)
                    .join(', ');

                const total = document.createElement('p');
                total.className = 'price';
                total.textContent = money(booking.total_amount);

                card.append(title, status, hotel, dates, room, total);
                $('bookingsList').appendChild(card);
            });
            if (currentLanguage === 'ja') applyLanguage();
        } catch (error) {
            showMessage($('bookingsMessage'), error.message, 'error');
        }
    }

    $('searchForm').addEventListener('submit', searchAvailability);

    $('accountButton').addEventListener('click', () => {
        if (token) {
            openAccount('login');
            showMessage($('accountMessage'), 'You are signed in. You can view your bookings or continue your selected reservation.', 'success');
        } else {
            openAccount('login');
        }
    });

    $('showLogin').addEventListener('click', () => openAccount('login'));
    $('showRegister').addEventListener('click', () => openAccount('register'));
    $('myBookingsButton').addEventListener('click', loadBookings);
    $('refreshBookingsButton').addEventListener('click', loadBookings);

    $('logoutButton').addEventListener('click', async () => {
        try {
            await api('/logout', { method: 'POST' });
        } catch {
            // Clear the local session even if the token has already expired.
        }

        token = null;
        sessionStorage.removeItem(TOKEN_KEY);
        updateAccountUI();
        hide($('myBookingsPanel'));
        hide($('confirmationPanel'));
        openAccount('login');
        showMessage($('accountMessage'), 'You have signed out.', 'success');
    });

    $('loginForm').addEventListener('submit', async event => {
        event.preventDefault();

        const button = $('loginForm').querySelector('button[type="submit"]');
        button.disabled = true;
        showMessage($('accountMessage'), 'Signing in...');

        try {
            const result = await api('/login', {
                method: 'POST',
                body: JSON.stringify({
                    email: $('loginEmail').value.trim(),
                    password: $('loginPassword').value
                })
            });

            token = result.token;
            sessionStorage.setItem(TOKEN_KEY, token);
            updateAccountUI();
            hide($('accountPanel'));

            if (selection) {
                displaySelection();
                showMessage($('bookingMessage'), 'Signed in. Review and confirm your reservation.', 'success');
            } else {
                showMessage($('searchMessage'), `Welcome back, ${result.user.first_name}!`, 'success');
            }
        } catch (error) {
            showMessage($('accountMessage'), error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    $('registerForm').addEventListener('submit', async event => {
        event.preventDefault();

        if ($('registerPassword').value !== $('confirmPassword').value) {
            showMessage($('accountMessage'), 'Passwords do not match.', 'error');
            return;
        }

        const button = $('registerForm').querySelector('button[type="submit"]');
        button.disabled = true;
        showMessage($('accountMessage'), 'Creating your account...');

        try {
            const result = await api('/register', {
                method: 'POST',
                body: JSON.stringify({
                    first_name: $('firstName').value.trim(),
                    last_name: $('lastName').value.trim(),
                    email: $('registerEmail').value.trim(),
                    phone: $('phone').value.trim(),
                    password: $('registerPassword').value,
                    password_confirmation: $('confirmPassword').value
                })
            });

            token = result.token;
            sessionStorage.setItem(TOKEN_KEY, token);
            updateAccountUI();
            hide($('accountPanel'));

            if (selection) {
                displaySelection();
                showMessage($('bookingMessage'), 'Account created. Review and confirm your reservation.', 'success');
            } else {
                showMessage($('searchMessage'), `Welcome, ${result.user.first_name}! Your account is ready.`, 'success');
            }
        } catch (error) {
            showMessage($('accountMessage'), error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    $('bookingForm').addEventListener('submit', async event => {
        event.preventDefault();

        if (!token) {
            openAccount('login');
            showMessage($('accountMessage'), 'Please sign in to confirm your booking.', 'error');
            return;
        }

        if (!selection) {
            showMessage($('bookingMessage'), 'Please search and choose a room first.', 'error');
            return;
        }

        const button = $('confirmBookingButton');
        button.disabled = true;
        showMessage($('bookingMessage'), 'Creating your booking...');

        try {
            const result = await api('/bookings', {
                method: 'POST',
                body: JSON.stringify({
                    check_in_date: selection.checkIn,
                    check_out_date: selection.checkOut,
                    room_type_id: selection.roomTypeId,
                    adults: selection.adults,
                    children: selection.children,
                    special_requests: $('specialRequests').value.trim() || null
                })
            });

            const booking = result.data;
            sessionStorage.removeItem(SELECTION_KEY);
            selection = null;

            showMessage(
                $('bookingMessage'),
                `${result.message} Your reference is ${booking.booking_reference}. Total: ${money(booking.total_amount)}. Status: ${booking.status}.`,
                'success'
            );

            $('bookingForm').reset();
            hide($('resultsPanel'));
            await loadBookings();
        } catch (error) {
            showMessage($('bookingMessage'), error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });

    $('checkIn').addEventListener('change', () => {
        if (!$('checkIn').value) return;

        const next = new Date(`${$('checkIn').value}T12:00:00`);
        next.setDate(next.getDate() + 1);
        const minimum = localDate(next);
        $('checkOut').min = minimum;

        if (!$('checkOut').value || $('checkOut').value < minimum) {
            $('checkOut').value = minimum;
        }
    });

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);

    const followingDay = new Date(tomorrow);
    followingDay.setDate(followingDay.getDate() + 1);

    $('checkIn').min = localDate(tomorrow);
    $('checkIn').value = localDate(tomorrow);
    $('checkOut').min = localDate(followingDay);
    $('checkOut').value = localDate(followingDay);

    updateAccountUI();
    applyLanguage();

    if (selection) {
        displaySelection();
    }
})();
</script>
</body>
</html>
