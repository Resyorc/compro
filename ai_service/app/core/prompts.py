def build_student_profile_prompt(data):
    student = data["student"]
    academic = data["academic"]
    attendance = data["attendance"]

    return f"""
Buat ringkasan profil siswa berdasarkan data berikut:

Nama: {student['name']}
Kelas: {student['class_name']}

Rata-rata nilai: {academic['average_score']}
Detail nilai per mapel: {academic['subjects']}

Kehadiran: {attendance['present_percentage']}%

Instruksi:
- Tulis dalam 2–3 paragraf.
- Bahasa formal, positif, dan mudah dibaca orang tua.
- Fokus pada kekuatan siswa.
- Hindari angka terlalu banyak.
"""

