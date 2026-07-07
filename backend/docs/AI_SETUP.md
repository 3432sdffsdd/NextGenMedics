# Study Resource Generation — API Setup

The **Learning Assistant** (Study tools tab) generates flashcards, revision notes, and MCQs from lecture files. It needs an **AI API key** in `backend/.env`.

## Quick setup (live server)

1. Open **`backend/.env`** on your server (create it from `.env.example` if missing).
2. Add or update these lines:

```env
AI_ENABLED=true
AI_API_KEY=sk-your-key-here
AI_BASE_URL=https://api.openai.com/v1
AI_MODEL=gpt-4o-mini
```

3. Save the file. **Do not upload `.env` to public folders** — it stays inside `backend/` only.
4. Try **Generate study resources** again in the teacher course → Study tools tab.

## Option A — OpenAI

1. Create an API key at [platform.openai.com](https://platform.openai.com/api-keys)
2. In `.env`:

```env
AI_API_KEY=sk-proj-...
AI_BASE_URL=https://api.openai.com/v1
AI_MODEL=gpt-4o-mini
```

## Option B — OpenRouter (many models, pay-as-you-go)

1. Sign up at [openrouter.ai](https://openrouter.ai) and create an API key
2. In `.env`:

```env
AI_API_KEY=sk-or-v1-...
AI_BASE_URL=https://openrouter.ai/api/v1
AI_MODEL=openai/gpt-4o-mini
```

## Option C — Groq (fast, free tier available)

```env
AI_API_KEY=gsk_...
AI_BASE_URL=https://api.groq.com/openai/v1
AI_MODEL=llama-3.3-70b-versatile
```

## Option D — Local Ollama (no cloud key)

Run [Ollama](https://ollama.com) on the same machine as the server:

```env
AI_API_KEY=
AI_BASE_URL=http://127.0.0.1:11434/v1
AI_MODEL=llama3.1
```

## Verify

From the server, or after deploying:

- Teacher → Course → **Study tools** → select a lecture
- If configured, the yellow setup warning disappears and **Generate study resources** works
- Or call `GET /backend/public/ai/status` while logged in as teacher/admin — `"ready": true`

## Troubleshooting

| Problem | Fix |
|---------|-----|
| "Add your AI API key" | Set `AI_API_KEY` in `backend/.env` and save |
| Still fails after adding key | Restart PHP/Apache; ensure `.env` is in `backend/` folder |
| Generation times out | Increase `AI_TIMEOUT=180` or use fewer flashcards/MCQs |
| No lecture file to read | Upload PDF/PPTX to the lecture first, or paste text manually |

Manual flashcards and MCQs still work without an API key (add them in the Review section).
