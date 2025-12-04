from fastapi import APIRouter
from app.schemas.summarize_schema import RequestData
from app.core.prompts import build_student_profile_prompt
from app.services.llm_client import chute_completion

router = APIRouter()

@router.post("/summarize-profile")
async def summarize_profile(payload: RequestData):
    # Unpack data
    student = payload.student
    academic = payload.academic
    attendance = payload.attendance

    # ------------------------------
    # 1. Hitung Academic Index
    avg_score = academic.get("average_score", 0)

    # ------------------------------
    # 2. Attendance Rate
    attendance_rate = attendance.get("present_percentage", 0)

    # ------------------------------
    # 3. Engagement Index
    engagement_index = min(
        100,
        (avg_score * 0.3)
        + (attendance_rate * 0.4)
    )

    # ------------------------------
    # 4. Segmentation
    if avg_score > 85 and attendance_rate > 90:
        segmentation = "High Performer"
    elif avg_score > 70 and attendance_rate > 80:
        segmentation = "Average Stable"
    else:
        segmentation = "Needs Attention"

    # ------------------------------
    # 5. Risk Level
    if avg_score >= 75 and attendance_rate >= 85:
        risk_level = "rendah"
    elif avg_score >= 60:
        risk_level = "sedang"
    else:
        risk_level = "tinggi"

    # ------------------------------
    # 6. Build AI Prompt
    payload = {
        "student": student,
        "academic": academic,
        "attendance": attendance,
    }
    prompt = build_student_profile_prompt(payload)

    # ------------------------------
    # 7. Call LLM Provider
    llm = await chute_completion(prompt)

    # ------------------------------
    # 8. Merge structured data + AI output
    return {
        "summary": llm["summary"],
        "segmentation": segmentation,
        "risk_level": risk_level,
        "academic_index": avg_score,
        "attendance_rate": attendance_rate,
        "engagement_index": round(engagement_index, 2),
        # "recommendations": llm.get("recommendations", []),
    }