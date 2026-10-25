<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temairazu AI | Smart Hotel Assistant</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }

        header {
            background: #102a43;
            color: white;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-size: 23px;
            font-weight: bold;
        }

        .brand span {
            color: #63d6c5;
        }

        header a {
            color: white;
            text-decoration: none;
            border: 1px solid #7dded0;
            padding: 10px 16px;
            border-radius: 8px;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .hero {
            text-align: center;
            margin-bottom: 28px;
        }

        .badge {
            display: inline-block;
            background: #d9f8f1;
            color: #087f70;
            padding: 7px 14px;
            border-radius: 30px;
            font-size: 13px;
        }

        h1 {
            font-size: 36px;
            margin: 15px 0 10px;
        }

        .hero p {
            color: #64748b;
            line-height: 1.6;
        }

        .chat-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(15, 40, 70, 0.09);
            border: 1px solid #e5edf5;
        }

        .chat-header {
            padding: 20px 24px;
            background: #eaf5f7;
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .avatar {
            width: 46px;
            height: 46px;
            background: #102a43;
            color: white;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 22px;
        }

        .chat-header h3 {
            margin: 0 0 5px;
        }

        .status {
            color: #07856e;
            font-size: 13px;
        }

        #messages {
            height: 350px;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            max-width: 82%;
            padding: 14px 17px;
            border-radius: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .bot {
            align-self: flex-start;
            background: #eef3f8;
            border-bottom-left-radius: 4px;
        }

        .user {
            align-self: flex-end;
            background: #102a43;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .quick-prompts {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            padding: 0 24px 20px;
        }

        .quick-prompts button {
            border: 1px solid #cbd9e6;
            background: white;
            padding: 10px 13px;
            border-radius: 20px;
            cursor: pointer;
            color: #334e68;
        }

        .quick-prompts button:hover {
            background: #eaf5f7;
        }

        .input-area {
            display: flex;
            gap: 10px;
            padding: 18px 24px;
            border-top: 1px solid #e5edf5;
        }

        #userInput {
            flex: 1;
            min-width: 0;
            padding: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
        }

        #userInput:focus {
            border-color: #0f9b8e;
        }

        #sendButton {
            border: none;
            background: #0f9b8e;
            color: white;
            padding: 0 23px;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
        }

        #sendButton:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        .disclaimer {
            text-align: center;
            font-size: 12px;
            color: #8291a3;
            padding: 0 15px 20px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 25px auto;
            }

            h1 {
                font-size: 28px;
            }

            #messages {
                height: 380px;
                padding: 16px;
            }

            .message {
                max-width: 94%;
            }

            .input-area {
                padding: 14px;
            }

            #sendButton {
                padding: 0 15px;
            }

            header {
                padding: 16px 5%;
            }
        }
    </style>
</head>

<body>
<header>
    <div class="brand">Temairazu <span>AI</span></div>
    <a href="{{ route('customer.book') }}">Book a Room</a>
</header>

<main class="container">
    <section class="hero">
        <span class="badge">SMART HOTEL BOOKING ASSISTANT</span>
        <h1>Your stay, made simpler.</h1>
        <p>
            Ask questions, explore booking options, and get help planning
            your hotel stay with Temairazu AI.
        </p>
    </section>

    <section class="chat-card">
        <div class="chat-header">
            <div class="avatar">AI</div>
            <div>
                <h3>Temairazu AI Assistant</h3>
                <div class="status">● Ready to assist</div>
            </div>
        </div>

        <div id="messages" aria-live="polite">
            <div class="message bot">
                Hello! Welcome to Temairazu AI.

                I can help you understand the booking process, explore room options, and answer hotel-related questions.

                What would you like help with today?
            </div>
        </div>

        <div class="quick-prompts">
            <button type="button" data-prompt="Explain how to book a room.">
                How do I book?
            </button>

            <button type="button" data-prompt="Help me find a room that fits my budget.">
                Find a room
            </button>

            <button type="button" data-prompt="What hotel policies should I check before booking?">
                Hotel policies
            </button>
        </div>

        <form id="chatForm" class="input-area">
            <input
                id="userInput"
                type="text"
                placeholder="Ask Temairazu AI anything..."
                autocomplete="off"
                maxlength="1000"
                required
            >

            <button id="sendButton" type="submit">Send</button>
        </form>

        <div class="disclaimer">
            AI-generated guidance may need verification. Confirm prices,
            availability, and hotel policies with the booking system.
        </div>
    </section>
</main>

<script>
    const messages = document.getElementById('messages');
    const input = document.getElementById('userInput');
    const form = document.getElementById('chatForm');
    const sendButton = document.getElementById('sendButton');

    function addMessage(text, sender) {
        const message = document.createElement('div');
        message.className = `message ${sender}`;

        // Treat all message content as text to prevent HTML injection.
        message.textContent = text;

        messages.appendChild(message);
        messages.scrollTop = messages.scrollHeight;

        return message;
    }

    function sendMessage(text) {
        input.value = text;
        form.requestSubmit();
    }

    document.querySelectorAll('[data-prompt]').forEach(button => {
        button.addEventListener('click', () => {
            sendMessage(button.dataset.prompt);
        });
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const text = input.value.trim();

        if (!text || sendButton.disabled) {
            return;
        }

        addMessage(text, 'user');
        input.value = '';
        sendButton.disabled = true;

        const thinking = addMessage('Temairazu AI is thinking...', 'bot');

        try {
            const response = await fetch('/api/temairazu-ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: text })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'The assistant is temporarily unavailable.');
            }

            thinking.textContent = data.reply || 'I could not generate a response. Please try again.';
        } catch (error) {
            thinking.textContent =
                error.message || 'Unable to connect. Please try again.';
        } finally {
            sendButton.disabled = false;
            input.focus();
        }
    });
</script>
</body>
</html>