import os
from dotenv import load_dotenv

load_dotenv()

CHUTES_API_KEY = os.getenv("CHUTES_API_KEY", "")
AI_PROVIDER = os.getenv("AI_PROVIDER", "deepseek")
DEEPSEEK_MODEL = os.getenv("DEEPSEEK_MODEL", "deepseek-ai/DeepSeek-R1")

API_URL = "https://llm.chutes.ai/v1/chat/completions"
