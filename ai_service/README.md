## SmartSchool AI Profiling Service

Layanan Python mandiri berbasis FastAPI yang menerima data nilai dan kehadiran dari aplikasi Laravel, kemudian menghasilkan ringkasan profiling sederhana untuk kebutuhan analitik AI.

### Menjalankan Secara Lokal

```bash
cd ai_service
python -m venv .venv
source .venv/bin/activate  # Windows: .venv\Scripts\activate
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

Secara default layanan berjalan di `http://127.0.0.1:8001` dan menyediakan health check pada `/health`.

### Kontrak Endpoint

- **Method / Path**: `POST /api/v1/profile`
- **Request Body**:

```json
{
  "student": {"id": 10, "name": "Ratri Ayu", "class_name": "XI IPA 1"},
  "grades": [
    {"subject": "Matematika", "score": 86},
    {"subject": "Fisika", "score": 78, "weight": 1.2}
  ],
  "attendance": {
    "total_sessions": 40,
    "present": 36,
    "sick": 2,
    "excused": 1,
    "unexcused": 1
  }
}
```

- **Response**:

```json
{
  "student": {"id": 10, "name": "Ratri Ayu", "class_name": "XI IPA 1"},
  "academic_index": 82.4,
  "attendance_rate": 90.0,
  "engagement_index": 85.56,
  "risk_level": "moderate",
  "segmentation": "consistent",
  "summary": "Ratri Ayu berada pada segmen consistent ...",
  "recommendations": [
    {"category": "academic", "message": "Fokuskan remedi ..."}
  ]
}
```

Profiling yang dihasilkan bersifat heuristik dan dapat diganti dengan model ML yang lebih canggih di masa depan. Laravel cukup menyesuaikan payload tanpa mengubah kontrak API.
