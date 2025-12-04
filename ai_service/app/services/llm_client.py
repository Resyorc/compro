import aiohttp
from app.core.config import CHUTES_API_KEY, API_URL, DEEPSEEK_MODEL

async def chute_completion(prompt: str):
    headers = {
        "Authorization": f"Bearer {CHUTES_API_KEY}",
        "Content-Type": "application/json"
    }

    body = {
        "model": DEEPSEEK_MODEL,
        "messages": [
            {"role": "user", "content": prompt}
        ],
        "stream": False,
        "max_tokens": 2048,
        "temperature": 0.7
    }

    async with aiohttp.ClientSession() as session:
        async with session.post(API_URL, headers=headers, json=body) as resp:
            data = await resp.json()

            return {
                "summary": data["choices"][0]["message"]["content"],
                "provider": "deepseek",
                "model": DEEPSEEK_MODEL,
                "tokens_used": data.get("usage", {}).get("total_tokens", 0)
            }
