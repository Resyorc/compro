from fastapi import FastAPI
from app.routers import summarize

app = FastAPI(title="AI Student Profile Service")

app.include_router(
    summarize.router,
    prefix="/api/v1",
    tags=["AI Summarizer"]
)

@app.get("/")
def home():
    return {"status": "running", "service": "AI Student Profile Service"}
