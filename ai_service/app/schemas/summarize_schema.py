from pydantic import BaseModel
from typing import List, Dict, Any, Optional

class RequestData(BaseModel):
    student: Dict[str, Any]
    academic: Dict[str, Any]
    attendance: Dict[str, Any]
    achievements: Optional[List[str]] = []
    behavior_notes: Optional[List[str]] = []
