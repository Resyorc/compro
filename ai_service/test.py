from __future__ import annotations

from typing import List, Optional

from fastapi import FastAPI
from pydantic import BaseModel, Field, validator


app = FastAPI(
    title="SmartSchool AI Profiling Service",
    version="1.0.0",
    description=(
        "Service mandiri untuk melakukan profiling AI sederhana "
        "berdasarkan nilai akademik dan data kehadiran siswa."
    ),
)


class StudentContext(BaseModel):
    id: int = Field(..., description="ID siswa pada sistem utama")
    name: str = Field(..., description="Nama siswa")
    class_name: Optional[str] = Field(None, description="Nama kelas / rombel")
    gender: Optional[str] = Field(None, description="Jenis kelamin")


class GradeInput(BaseModel):
    subject: str = Field(..., description="Nama mata pelajaran")
    score: float = Field(..., ge=0, le=100, description="Nilai akhir 0-100")
    weight: float = Field(1.0, ge=0, description="Bobot relatif nilai (default 1)")


class AttendanceInput(BaseModel):
    total_sessions: int = Field(..., gt=0, description="Jumlah pertemuan yang direncanakan")
    present: int = Field(..., ge=0, description="Jumlah hadir")
    sick: int = Field(0, ge=0, description="Jumlah sakit dengan surat")
    excused: int = Field(0, ge=0, description="Jumlah izin resmi")
    unexcused: int = Field(0, ge=0, description="Jumlah alpha/tanpa keterangan")

    @validator("present")
    def present_not_exceed_total(cls, value, values):
        total = values.get("total_sessions")
        if total is not None and value > total:
            raise ValueError("present tidak boleh melebihi total_sessions")
        return value

    @validator("sick", "excused", "unexcused")
    def absence_not_negative(cls, value):
        if value < 0:
            raise ValueError("angka absensi tidak boleh negatif")
        return value


class ProfilingRequest(BaseModel):
    student: StudentContext
    grades: List[GradeInput]
    attendance: AttendanceInput


class Recommendation(BaseModel):
    category: str
    message: str


class ProfilingResponse(BaseModel):
    student: StudentContext
    academic_index: float
    attendance_rate: float
    engagement_index: float
    risk_level: str
    segmentation: str
    summary: str
    recommendations: List[Recommendation]


@app.get("/health", tags=["health"])
def health_check():
    return {"status": "ok"}


@app.post("/api/v1/profile", response_model=ProfilingResponse, tags=["profiling"])
def generate_profile(payload: ProfilingRequest):
    academic_index = _weighted_average(payload.grades)
    attendance_rate = _attendance_percentage(payload.attendance)
    engagement_index = round(academic_index * 0.65 + attendance_rate * 0.35, 2)
    risk_level = _risk_label(academic_index, attendance_rate, payload.attendance.unexcused)
    segmentation = _segment(academic_index, attendance_rate)
    summary = _build_summary(
        payload.student.name,
        academic_index,
        attendance_rate,
        risk_level,
        segmentation,
    )
    recommendations = _build_recommendations(payload, academic_index, attendance_rate)

    return ProfilingResponse(
        student=payload.student,
        academic_index=academic_index,
        attendance_rate=attendance_rate,
        engagement_index=engagement_index,
        risk_level=risk_level,
        segmentation=segmentation,
        summary=summary,
        recommendations=recommendations,
    )


def _weighted_average(grades: List[GradeInput]) -> float:
    if not grades:
        return 0.0
    total_weight = sum(max(grade.weight, 0.001) for grade in grades)
    weighted_sum = sum(grade.score * max(grade.weight, 0.001) for grade in grades)
    return round(weighted_sum / total_weight, 2)


def _attendance_percentage(attendance: AttendanceInput) -> float:
    effective_total = max(attendance.total_sessions, 1)
    attended = min(attendance.present, effective_total)
    return round((attended / effective_total) * 100, 2)


def _risk_label(avg_grade: float, attendance_rate: float, unexcused: int) -> str:
    penalty = max(0, 100 - avg_grade) * 0.5 + max(0, 100 - attendance_rate) * 0.4
    penalty += unexcused * 2
    if penalty >= 50:
        return "high"
    if penalty >= 25:
        return "moderate"
    return "low"


def _segment(avg_grade: float, attendance_rate: float) -> str:
    if avg_grade >= 85 and attendance_rate >= 95:
        return "role-model"
    if avg_grade >= 70 and attendance_rate >= 85:
        return "consistent"
    if avg_grade >= 60 and attendance_rate >= 75:
        return "developing"
    return "watch-list"


def _build_summary(name: str, avg_grade: float, attendance_rate: float, risk: str, segment: str) -> str:
    return (
        f"{name} berada pada segmen {segment.replace('-', ' ')} "
        f"dengan rata-rata nilai {avg_grade} dan kehadiran {attendance_rate}%. "
        f"Tingkat risiko saat ini tergolong {risk}."
    )


def _build_recommendations(
    payload: ProfilingRequest, avg_grade: float, attendance_rate: float
) -> List[Recommendation]:
    recs: List[Recommendation] = []

    if avg_grade < 75:
        recs.append(
            Recommendation(
                category="academic",
                message="Fokuskan remedi pada mata pelajaran dengan skor di bawah 75 dan jadwalkan sesi tutoring mingguan.",
            )
        )

    low_subjects = [g.subject for g in payload.grades if g.score < 70]
    if low_subjects:
        joined = ", ".join(low_subjects[:3])
        recs.append(
            Recommendation(
                category="subject-priority",
                message=f"Perlu intervensi pada mapel: {joined}. Gunakan latihan berbasis proyek untuk meningkatkan pemahaman.",
            )
        )

    if attendance_rate < 90:
        recs.append(
            Recommendation(
                category="attendance",
                message="Perkuat komunikasi dengan orang tua dan buat target kehadiran mingguan minimal 95%.",
            )
        )

    if payload.attendance.unexcused >= 3:
        recs.append(
            Recommendation(
                category="counseling",
                message="Jadwalkan konseling untuk menggali penyebab ketidakhadiran tanpa keterangan.",
            )
        )

    if not recs:
        recs.append(
            Recommendation(
                category="maintenance",
                message="Pertahankan performa dengan memberikan tantangan akademik tambahan dan monitoring berkala.",
            )
        )

    return recs


if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=8001)
