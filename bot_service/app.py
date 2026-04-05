import os
import re
import unicodedata
from typing import Any, Dict, List, Optional, Tuple

import mysql.connector
from dotenv import load_dotenv
from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

try:
    from openai import OpenAI
except Exception:  # pragma: no cover
    OpenAI = None

load_dotenv()


class UserContext(BaseModel):
    ma_tk: int = 0
    username: str = ""
    role: str = ""
    permissions: List[str] = Field(default_factory=list)


class ChatMessage(BaseModel):
    role: str
    content: str


class ChatRequest(BaseModel):
    message: str = Field(min_length=1, max_length=1000)
    history: List[ChatMessage] = Field(default_factory=list)
    user: UserContext


class ChatResponse(BaseModel):
    reply: str
    actions: List[str] = Field(default_factory=list)
    suggestions: List[str] = Field(default_factory=list)
    action_draft: Optional[Dict[str, Any]] = None
    source: str = "bot_service"


class DataTools:
    def __init__(self) -> None:
        self.db_host = os.getenv("DB_HOST", "127.0.0.1")
        self.db_port = int(os.getenv("DB_PORT", "3306"))
        self.db_user = os.getenv("DB_USER", "root")
        self.db_password = os.getenv("DB_PASSWORD", "")
        self.db_name = os.getenv("DB_NAME", "quanlynhansu")

    def _connect(self):
        return mysql.connector.connect(
            host=self.db_host,
            port=self.db_port,
            user=self.db_user,
            password=self.db_password,
            database=self.db_name,
            charset="utf8",
        )

    def employee_count(self) -> int:
        conn = self._connect()
        try:
            cur = conn.cursor()
            cur.execute("SELECT COUNT(*) FROM nhanvien")
            row = cur.fetchone()
            return int((row or [0])[0])
        finally:
            conn.close()

    def pending_leave_count(self) -> int:
        conn = self._connect()
        try:
            cur = conn.cursor()
            cur.execute("SELECT COUNT(*) FROM nghiphep WHERE TrangThai = %s", ("Chờ duyệt",))
            row = cur.fetchone()
            return int((row or [0])[0])
        finally:
            conn.close()

    def search_employee(self, keyword: str, limit: int = 5) -> List[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            like = f"%{keyword}%"
            cur.execute(
                """
                SELECT MaNV, HoTen, Email, DienThoai, TrangThai
                FROM nhanvien
                WHERE CAST(MaNV AS CHAR) LIKE %s OR HoTen LIKE %s
                ORDER BY MaNV DESC
                LIMIT %s
                """,
                (like, like, limit),
            )
            rows = cur.fetchall() or []
            return rows
        finally:
            conn.close()

    def leave_status_summary(self) -> List[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            cur.execute(
                """
                SELECT TrangThai, COUNT(*) AS total
                FROM nghiphep
                GROUP BY TrangThai
                ORDER BY total DESC
                """
            )
            return cur.fetchall() or []
        finally:
            conn.close()

    def leave_request_detail(self, ma_np: int) -> Optional[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            cur.execute(
                """
                SELECT np.MaNP, np.MaNV, np.TuNgay, np.DenNgay, np.SoNgayNghi, np.LoaiNghi, np.TrangThai, nv.HoTen
                FROM nghiphep np
                JOIN nhanvien nv ON np.MaNV = nv.MaNV
                WHERE np.MaNP = %s
                LIMIT 1
                """,
                (ma_np,),
            )
            return cur.fetchone()
        finally:
            conn.close()

    def contracts_expiring(self, days: int = 30, limit: int = 5) -> List[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            cur.execute(
                """
                SELECT hd.SoHopDong, hd.NgayKetThuc, nv.HoTen
                FROM hopdong hd
                LEFT JOIN nhanvien nv ON hd.MaNV = nv.MaNV
                WHERE hd.TrangThai = %s
                  AND hd.NgayKetThuc IS NOT NULL
                  AND hd.NgayKetThuc <= DATE_ADD(CURDATE(), INTERVAL %s DAY)
                  AND hd.NgayKetThuc >= CURDATE()
                ORDER BY hd.NgayKetThuc ASC
                LIMIT %s
                """,
                ("Còn hiệu lực", days, limit),
            )
            return cur.fetchall() or []
        finally:
            conn.close()

    def department_headcount(self, limit: int = 6) -> List[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            cur.execute(
                """
                SELECT pb.TenPB, COUNT(DISTINCT pc.MaNV) AS total
                FROM phancong pc
                INNER JOIN phongban pb ON pc.MaPB = pb.MaPB
                WHERE pc.NgayKetThuc IS NULL OR pc.NgayKetThuc >= CURDATE()
                GROUP BY pb.MaPB, pb.TenPB
                ORDER BY total DESC
                LIMIT %s
                """,
                (limit,),
            )
            return cur.fetchall() or []
        finally:
            conn.close()

    def employee_detail(self, keyword: str) -> Optional[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            like = f"%{keyword}%"
            cur.execute(
                """
                SELECT nv.MaNV, nv.HoTen, nv.Email, nv.DienThoai, nv.TrangThai,
                       pb.TenPB, cv.TenCV
                FROM nhanvien nv
                LEFT JOIN phancong pc ON pc.MaNV = nv.MaNV AND (pc.NgayKetThuc IS NULL OR pc.NgayKetThuc >= CURDATE())
                LEFT JOIN phongban pb ON pc.MaPB = pb.MaPB
                LEFT JOIN chucvu cv ON pc.MaCV = cv.MaCV
                WHERE CAST(nv.MaNV AS CHAR) LIKE %s OR nv.HoTen LIKE %s
                ORDER BY nv.MaNV DESC
                LIMIT 1
                """,
                (like, like),
            )
            return cur.fetchone()
        finally:
            conn.close()

    def recruitment_status_summary(self) -> Optional[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            cur.execute(
                """
                SELECT
                    COUNT(*) AS Tong,
                    SUM(CASE WHEN TrangThai='Nộp hồ sơ' THEN 1 ELSE 0 END) AS NopHoSo,
                    SUM(CASE WHEN TrangThai='Sàng lọc' THEN 1 ELSE 0 END) AS SangLoc,
                    SUM(CASE WHEN TrangThai='Phỏng vấn' THEN 1 ELSE 0 END) AS PhongVan,
                    SUM(CASE WHEN TrangThai='Offer' THEN 1 ELSE 0 END) AS Offer,
                    SUM(CASE WHEN TrangThai='Nhận việc' THEN 1 ELSE 0 END) AS NhanViec,
                    SUM(CASE WHEN TrangThai='Rớt' THEN 1 ELSE 0 END) AS Rot
                FROM hosoungtuyen
                """
            )
            return cur.fetchone()
        finally:
            conn.close()

    def top_candidates(self, limit: int = 5) -> List[Dict[str, Any]]:
        conn = self._connect()
        try:
            cur = conn.cursor(dictionary=True)
            cur.execute(
                """
                SELECT uv.HoTen,
                       ROUND((dg.DiemKyNang + dg.DiemKinhNghiem + dg.DiemThaiDo) / 3, 2) AS DiemTB
                FROM danhgiaphongvan dg
                JOIN hosoungtuyen hs ON dg.MaHS = hs.MaHS
                JOIN ungvien uv ON hs.MaUV = uv.MaUV
                ORDER BY DiemTB DESC
                LIMIT %s
                """,
                (limit,),
            )
            return cur.fetchall() or []
        finally:
            conn.close()


def normalize_text(text: str) -> str:
    lowered = text.lower().strip()
    normalized = unicodedata.normalize("NFD", lowered)
    no_marks = "".join(ch for ch in normalized if unicodedata.category(ch) != "Mn")
    return no_marks.replace("đ", "d")


def is_pending_leave_status(status_value: Any) -> bool:
    return normalize_text(str(status_value or "")) == "cho duyet"


class ChatEngine:
    def __init__(self) -> None:
        self.api_key = os.getenv("OPENAI_API_KEY", "").strip()
        self.model = os.getenv("OPENAI_MODEL", "gpt-4o-mini").strip()
        self.client = OpenAI(api_key=self.api_key) if self.api_key and OpenAI else None
        self.tools = DataTools()

    def answer(self, request: ChatRequest) -> ChatResponse:
        message = request.message.strip()

        plan_reply = self._try_action_plan(message)
        if plan_reply is not None:
            return ChatResponse(
                reply=plan_reply[0],
                actions=plan_reply[1],
                suggestions=plan_reply[2],
                action_draft=plan_reply[3],
                source="action_plan",
            )

        tool_reply = self._try_tool_answer(message, request.user.permissions)
        if tool_reply is not None:
            return ChatResponse(reply=tool_reply[0], actions=tool_reply[1], suggestions=tool_reply[2], source="tool")

        llm_reply = self._llm_answer(request)
        if llm_reply:
            return ChatResponse(
                reply=llm_reply,
                actions=["Tư vấn chế độ chỉ đọc", "Chưa thực thi thay đổi dữ liệu"],
                suggestions=self._default_suggestions(),
                source="llm",
            )

        fallback = (
            "Tôi đã nhận câu hỏi của bạn. Hiện tại dịch vụ LLM chưa được cấu hình, "
            "nhưng bạn vẫn có thể dùng các lệnh tra cứu dữ liệu như tổng nhân viên, đơn nghỉ phép chờ duyệt, "
            "thống kê nghỉ phép, hợp đồng sắp hết hạn, tìm nhân viên."
        )
        return ChatResponse(
            reply=fallback,
            actions=["Cấu hình OPENAI_API_KEY để bật trợ lý nâng cao"],
            suggestions=self._default_suggestions(),
            source="fallback",
        )

    def _default_suggestions(self) -> List[str]:
        return [
            "Tổng số nhân viên hiện tại là bao nhiêu?",
            "Thống kê nghỉ phép",
            "Hợp đồng sắp hết hạn",
        ]

    def _try_action_plan(self, message: str) -> Optional[Tuple[str, List[str], List[str], Optional[Dict[str, Any]]]]:
        q = normalize_text(message)
        write_verbs = ["tao", "them", "xoa", "cap nhat", "duyet", "cham dut", "reset", "doi mat khau"]

        if not any(token in q for token in write_verbs):
            return None

        if "duyet" in q and "nghi phep" in q:
            match = re.search(r"ma\s+(\d+)", q)
            ma_np = int(match.group(1)) if match else 0
            if ma_np > 0:
                detail = self.tools.leave_request_detail(ma_np)
                if detail:
                    if not is_pending_leave_status(detail.get("TrangThai")):
                        return (
                            (
                                f"Đơn #{detail.get('MaNP')} hiện đang ở trạng thái {detail.get('TrangThai')}, "
                                "không thể duyệt lại."
                            ),
                            [
                                "Không tạo action draft vì trạng thái không hợp lệ",
                                "Chỉ có thể duyệt đơn ở trạng thái Chờ duyệt",
                            ],
                            ["Thống kê nghỉ phép", "Có bao nhiêu đơn nghỉ phép chờ duyệt?", "Tìm nhân viên 12"],
                            None,
                        )

                    return (
                        (
                            "Tôi đã chuẩn bị bản xác nhận duyệt nghỉ phép. "
                            f"Đơn #{detail.get('MaNP')} của {detail.get('HoTen')} từ {detail.get('TuNgay')} đến {detail.get('DenNgay')} "
                            f"({detail.get('SoNgayNghi')} ngày) hiện ở trạng thái {detail.get('TrangThai')}."
                        ),
                        [
                            "Kiểm tra lại thông tin đơn trước khi xác nhận",
                            "Sau khi xác nhận, chatbot sẽ gọi chức năng duyệt đơn",
                        ],
                        ["Xác nhận duyệt đơn này", "Thống kê nghỉ phép", "Có bao nhiêu đơn nghỉ phép chờ duyệt?"],
                        {
                            "action_type": "leave_approve",
                            "title": f"Duyệt đơn nghỉ phép #{detail.get('MaNP')}",
                            "summary": (
                                f"Nhân viên: {detail.get('HoTen')} | Từ {detail.get('TuNgay')} đến {detail.get('DenNgay')} | "
                                f"Loại: {detail.get('LoaiNghi')} | Trạng thái hiện tại: {detail.get('TrangThai')}"
                            ),
                            "required_permission": "duyet_nghiphep",
                            "confirm_label": "Xác nhận duyệt đơn",
                            "payload": {"ma_np": ma_np},
                        },
                    )

            return (
                "Yêu cầu này nên đi qua quy trình duyệt an toàn: xác định mã đơn nghỉ phép, kiểm tra trạng thái hiện tại, xem phạm vi ngày nghỉ bị ảnh hưởng, sau đó mới xác nhận duyệt.",
                [
                    "Chưa duyệt đơn thực tế",
                    "Cần có: mã đơn nghỉ phép hoặc mã nhân viên + khoảng ngày",
                ],
                [
                    "Thống kê nghỉ phép",
                    "Có bao nhiêu đơn nghỉ phép chờ duyệt?",
                    "Hãy lập action plan duyệt nghỉ phép mã 5",
                ],
                None,
            )

        if ("tu choi" in q or "tuchoi" in q) and "nghi phep" in q:
            match = re.search(r"ma\s+(\d+)", q)
            ma_np = int(match.group(1)) if match else 0
            if ma_np > 0:
                detail = self.tools.leave_request_detail(ma_np)
                if detail:
                    if not is_pending_leave_status(detail.get("TrangThai")):
                        return (
                            (
                                f"Đơn #{detail.get('MaNP')} hiện đang ở trạng thái {detail.get('TrangThai')}, "
                                "không thể từ chối ở bước này."
                            ),
                            [
                                "Không tạo action draft vì trạng thái không hợp lệ",
                                "Chỉ có thể từ chối đơn ở trạng thái Chờ duyệt",
                            ],
                            ["Thống kê nghỉ phép", "Có bao nhiêu đơn nghỉ phép chờ duyệt?", "Tìm nhân viên 12"],
                            None,
                        )

                    return (
                        (
                            "Tôi đã chuẩn bị bản xác nhận từ chối nghỉ phép. "
                            f"Đơn #{detail.get('MaNP')} của {detail.get('HoTen')} từ {detail.get('TuNgay')} đến {detail.get('DenNgay')} "
                            f"({detail.get('SoNgayNghi')} ngày) hiện ở trạng thái {detail.get('TrangThai')}."
                        ),
                        [
                            "Kiểm tra lại thông tin đơn trước khi xác nhận",
                            "Sau khi xác nhận, chatbot sẽ gọi chức năng từ chối đơn",
                        ],
                        ["Xác nhận từ chối đơn này", "Thống kê nghỉ phép", "Có bao nhiêu đơn nghỉ phép chờ duyệt?"],
                        {
                            "action_type": "leave_reject",
                            "title": f"Từ chối đơn nghỉ phép #{detail.get('MaNP')}",
                            "summary": (
                                f"Nhân viên: {detail.get('HoTen')} | Từ {detail.get('TuNgay')} đến {detail.get('DenNgay')} | "
                                f"Loại: {detail.get('LoaiNghi')} | Trạng thái hiện tại: {detail.get('TrangThai')}"
                            ),
                            "required_permission": "tuchoi_nghiphep",
                            "confirm_label": "Xác nhận từ chối đơn",
                            "payload": {"ma_np": ma_np},
                        },
                    )

            return (
                "Yêu cầu từ chối nghỉ phép cần có mã đơn cụ thể để tôi chuẩn bị bản xác nhận an toàn.",
                ["Chưa từ chối đơn thực tế", "Ví dụ: từ chối nghỉ phép mã 5"],
                ["Thống kê nghỉ phép", "Có bao nhiêu đơn nghỉ phép chờ duyệt?"],
                None,
            )

        if ("tao" in q or "them" in q) and "phan cong" in q:
            return (
                "Tôi có thể hỗ trợ tạo kế hoạch phân công an toàn. Trình tự nên là: xác định nhân viên, phòng ban, chức vụ, ngày bắt đầu, lý do điều chuyển, sau đó hiển thị bản xem trước trước khi lưu.",
                [
                    "Chưa tạo dữ liệu thật",
                    "Cần xác định: Mã NV, phòng ban, chức vụ, ngày bắt đầu, loại điều chuyển",
                ],
                [
                    "Hãy lập action plan tạo phân công cho nhân viên mã 12",
                    "Tìm nhân viên 12",
                    "Phân bổ nhân sự theo phòng ban",
                ],
                None,
            )

        if ("tao" in q or "them" in q) and ("lich phong van" in q or "phong van" in q):
            return (
                "Tôi có thể hỗ trợ quy trình tạo lịch phỏng vấn: xác định hồ sơ ứng tuyển, ngày, giờ, địa điểm, ghi chú và kiểm tra lịch trống trước khi xác nhận.",
                [
                    "Chưa tạo lịch phỏng vấn thật",
                    "Cần có: mã hồ sơ ứng tuyển, ngày, giờ, địa điểm",
                ],
                [
                    "Tóm tắt tuyển dụng",
                    "Top ứng viên hiện tại",
                    "Hãy lập action plan tạo lịch phỏng vấn cho hồ sơ 8",
                ],
                None,
            )

        return (
            "Yêu cầu của bạn có thể ảnh hưởng đến dữ liệu hệ thống. Tôi đề xuất chuyển sang chế độ Action an toàn: "
            "(1) xác định chức năng cần thực hiện, (2) kiểm tra quyền tài khoản, (3) xem trước dữ liệu sẽ thay đổi, "
            "(4) bạn xác nhận rồi mới thực thi.",
            [
                "Chưa thực thi thao tác ghi/xóa",
                "Phản hồi: xac nhan + mo ta cu the để tạo action plan chi tiết",
            ],
            self._default_suggestions(),
            None,
        )

    def _permission_denied(self, permission: str) -> Tuple[str, List[str], List[str]]:
        return (
            "Tôi không thể truy cập dữ liệu này vì tài khoản hiện tại chưa có quyền phù hợp.",
            [f"Quyền yêu cầu: {permission}", "Hãy liên hệ quản trị viên để được cấp quyền"],
            self._default_suggestions(),
        )

    def _has_permission(self, user_permissions: List[str], permission: str) -> bool:
        return permission in set(user_permissions or [])

    def _try_tool_answer(self, message: str, user_permissions: List[str]) -> Optional[Tuple[str, List[str], List[str]]]:
        q = normalize_text(message)

        if "tong" in q and "nhan vien" in q:
            if not self._has_permission(user_permissions, "xem_nhanvien"):
                return self._permission_denied("xem_nhanvien")

            count = self.tools.employee_count()
            return (
                f"Tổng số nhân viên hiện tại là {count}.",
                ["Nguồn: bảng nhanvien", "Bạn có thể hỏi thêm theo phòng ban"],
                ["Phân bổ nhân sự theo phòng ban", "Tìm nhân viên 12", "Chi tiết nhân viên Nguyễn Văn A"],
            )

        if "nghi phep" in q and "cho duyet" in q:
            if not self._has_permission(user_permissions, "xem_nghiphep"):
                return self._permission_denied("xem_nghiphep")

            count = self.tools.pending_leave_count()
            return (
                f"Số đơn nghỉ phép đang chờ duyệt là {count}.",
                ["Nguồn: bảng nghiphep", "Có thể lọc theo ngày nếu cần"],
                ["Thống kê nghỉ phép", "Hãy lập action plan duyệt nghỉ phép mã 5", "Tổng số nhân viên hiện tại là bao nhiêu?"],
            )

        if "thong ke" in q and "nghi phep" in q:
            if not self._has_permission(user_permissions, "xem_nghiphep"):
                return self._permission_denied("xem_nghiphep")

            rows = self.tools.leave_status_summary()
            if not rows:
                return ("Chưa có dữ liệu nghỉ phép để thống kê.", ["Nguồn: bảng nghiphep"], ["Có bao nhiêu đơn nghỉ phép chờ duyệt?", "Tổng số nhân viên hiện tại là bao nhiêu?"])

            lines = ["Tổng quan trạng thái nghỉ phép:"]
            for row in rows:
                lines.append(f"- {row.get('TrangThai')}: {row.get('total')} đơn")

            return (
                "\n".join(lines),
                ["Nguồn: bảng nghiphep", "Bao gồm tất cả trạng thái"],
                ["Có bao nhiêu đơn nghỉ phép chờ duyệt?", "Hợp đồng sắp hết hạn", "Tóm tắt tuyển dụng"],
            )

        if "hop dong" in q and ("sap het han" in q or "het han" in q or "30 ngay" in q):
            if not self._has_permission(user_permissions, "xem_hopdong"):
                return self._permission_denied("xem_hopdong")

            rows = self.tools.contracts_expiring(days=30, limit=5)
            if not rows:
                return (
                    "Không có hợp đồng còn hiệu lực sắp hết hạn trong 30 ngày tới.",
                    ["Nguồn: bảng hopdong", "Điều kiện: TrangThai còn hiệu lực"],
                    ["Tổng số nhân viên hiện tại là bao nhiêu?", "Phân bổ nhân sự theo phòng ban", "Tóm tắt tuyển dụng"],
                )

            lines = ["Top hợp đồng sắp hết hạn (30 ngày):"]
            for row in rows:
                lines.append(
                    f"- {row.get('SoHopDong')} | {row.get('HoTen')} | Hết hạn: {row.get('NgayKetThuc')}"
                )
            return (
                "\n".join(lines),
                ["Nguồn: bảng hopdong", "Top 5 sắp hết hạn gần nhất"],
                ["Tìm nhân viên 12", "Phân bổ nhân sự theo phòng ban", "Thống kê nghỉ phép"],
            )

        if "phong ban" in q and ("phan bo" in q or "nhieu nhat" in q or "bao nhieu" in q):
            if not self._has_permission(user_permissions, "xem_phancong"):
                return self._permission_denied("xem_phancong")

            rows = self.tools.department_headcount(limit=6)
            if not rows:
                return ("Chưa có dữ liệu phân công để thống kê phòng ban.", ["Nguồn: bảng phancong"], ["Tổng số nhân viên hiện tại là bao nhiêu?", "Hợp đồng sắp hết hạn"])

            lines = ["Phân bổ nhân sự theo phòng ban (phân công đang hiệu lực):"]
            for row in rows:
                lines.append(f"- {row.get('TenPB')}: {row.get('total')} nhân viên")

            return (
                "\n".join(lines),
                ["Nguồn: phancong + phongban", "Top 6 phòng ban"],
                ["Tổng số nhân viên hiện tại là bao nhiêu?", "Tìm nhân viên 12", "Hợp đồng sắp hết hạn"],
            )

        if "chi tiet nhan vien" in q or "thong tin nhan vien" in q:
            if not self._has_permission(user_permissions, "xem_nhanvien"):
                return self._permission_denied("xem_nhanvien")

            keyword = re.sub(r"^(chi tiet nhan vien|thong tin nhan vien)\s+", "", q).strip()
            if not keyword:
                return (
                    "Bạn cần cung cấp tên hoặc mã nhân viên để tôi tra cứu chi tiết.",
                    ["Ví dụ: chi tiết nhân viên 12", "Ví dụ: thông tin nhân viên nguyễn văn a"],
                    ["Tìm nhân viên 12", "Tổng số nhân viên hiện tại là bao nhiêu?"],
                )

            row = self.tools.employee_detail(keyword)
            if not row:
                return (
                    "Không tìm thấy nhân viên phù hợp để xem chi tiết.",
                    ["Thử lại với mã NV hoặc tên gần đúng"],
                    ["Tìm nhân viên 12", "Phân bổ nhân sự theo phòng ban"],
                )

            reply = (
                f"Thông tin nhân viên:\n"
                f"- Mã NV: {row.get('MaNV')}\n"
                f"- Họ tên: {row.get('HoTen')}\n"
                f"- Trạng thái: {row.get('TrangThai')}\n"
                f"- Phòng ban hiện tại: {row.get('TenPB') or 'Chưa có'}\n"
                f"- Chức vụ hiện tại: {row.get('TenCV') or 'Chưa có'}\n"
                f"- Email: {row.get('Email') or 'Chưa có'}\n"
                f"- Điện thoại: {row.get('DienThoai') or 'Chưa có'}"
            )
            return (
                reply,
                ["Nguồn: nhanvien + phancong hiện tại"],
                ["Tìm nhân viên 12", "Phân bổ nhân sự theo phòng ban", "Hợp đồng sắp hết hạn"],
            )

        if "tom tat tuyen dung" in q or ("thong ke" in q and "tuyen dung" in q):
            if not self._has_permission(user_permissions, "xem_dot_tuyen"):
                return self._permission_denied("xem_dot_tuyen")

            row = self.tools.recruitment_status_summary()
            if not row:
                return (
                    "Chưa có dữ liệu tuyển dụng để tổng hợp.",
                    ["Nguồn: bảng hosoungtuyen"],
                    ["Top ứng viên", "Tổng số nhân viên hiện tại là bao nhiêu?"],
                )

            reply = (
                "Tóm tắt tuyển dụng hiện tại:\n"
                f"- Tổng hồ sơ: {row.get('Tong', 0)}\n"
                f"- Nộp hồ sơ: {row.get('NopHoSo', 0)}\n"
                f"- Sàng lọc: {row.get('SangLoc', 0)}\n"
                f"- Phỏng vấn: {row.get('PhongVan', 0)}\n"
                f"- Offer: {row.get('Offer', 0)}\n"
                f"- Nhận việc: {row.get('NhanViec', 0)}\n"
                f"- Rớt: {row.get('Rot', 0)}"
            )
            return (
                reply,
                ["Nguồn: bảng hosoungtuyen"],
                ["Top ứng viên", "Hãy lập action plan tạo lịch phỏng vấn cho hồ sơ 8", "Hợp đồng sắp hết hạn"],
            )

        if "top ung vien" in q or "ung vien tot nhat" in q:
            if not self._has_permission(user_permissions, "xem_dot_tuyen"):
                return self._permission_denied("xem_dot_tuyen")

            rows = self.tools.top_candidates(limit=5)
            if not rows:
                return (
                    "Chưa có dữ liệu đánh giá phỏng vấn để xếp hạng ứng viên.",
                    ["Nguồn: bảng danhgiaphongvan"],
                    ["Tóm tắt tuyển dụng", "Hãy lập action plan tạo lịch phỏng vấn cho hồ sơ 8"],
                )

            lines = ["Top ứng viên theo điểm đánh giá:"]
            for idx, row in enumerate(rows, start=1):
                lines.append(f"- #{idx}: {row.get('HoTen')} | Điểm TB: {row.get('DiemTB')}")
            return (
                "\n".join(lines),
                ["Nguồn: danhgiaphongvan + hosoungtuyen + ungvien"],
                ["Tóm tắt tuyển dụng", "Hãy lập action plan tạo lịch phỏng vấn cho hồ sơ 8", "Tổng số nhân viên hiện tại là bao nhiêu?"],
            )

        m = re.search(r"tim nhan vien\s+(.+)", q)
        if m:
            if not self._has_permission(user_permissions, "xem_nhanvien"):
                return self._permission_denied("xem_nhanvien")

            keyword = m.group(1).strip()
            rows = self.tools.search_employee(keyword, limit=5)
            if not rows:
                return (
                    "Không tìm thấy nhân viên phù hợp với từ khóa.",
                    ["Thử lại với tên hoặc mã NV khác"],
                    ["Chi tiết nhân viên 12", "Tổng số nhân viên hiện tại là bao nhiêu?"],
                )

            lines = ["Đã tìm thấy một số nhân viên:"]
            for row in rows:
                lines.append(
                    f"- MaNV {row.get('MaNV')} | {row.get('HoTen')} | {row.get('TrangThai')}"
                )
            return (
                "\n".join(lines),
                ["Nguồn: bảng nhanvien", "Kết quả tối đa 5 dòng"],
                [f"Chi tiết nhân viên {keyword}", "Phân bổ nhân sự theo phòng ban", "Hợp đồng sắp hết hạn"],
            )

        return None

    def _llm_answer(self, request: ChatRequest) -> str:
        if not self.client:
            return ""

        system_prompt = (
            "Bạn là trợ lý HR nội bộ cho hệ thống quản lý nhân sự. "
            "Luôn trả lời bằng tiếng Việt rõ ràng, ngắn gọn, thực tế. "
            "Không bao giờ khẳng định đã thực thi thao tác ghi/xóa dữ liệu. "
            "Nếu người dùng yêu cầu thao tác rủi ro, hãy đưa quy trình an toàn và yêu cầu xác nhận. "
            "Ưu tiên gợi ý dạng các bước có đánh số khi phù hợp. "
            "Khi có thể, hãy nêu rõ nguồn dữ liệu hoặc giả định bạn đang dùng."
        )

        messages: List[Dict[str, str]] = [{"role": "system", "content": system_prompt}]

        for item in request.history[-8:]:
            role = item.role if item.role in {"user", "assistant"} else "user"
            messages.append({"role": role, "content": item.content})

        context_line = (
            f"User context: username={request.user.username}, role={request.user.role}, "
            f"permission_count={len(request.user.permissions)}"
        )
        messages.append({"role": "system", "content": context_line})
        messages.append({"role": "user", "content": request.message})

        try:
            completion = self.client.chat.completions.create(
                model=self.model,
                messages=messages,
                temperature=0.3,
                max_tokens=700,
            )
            content = completion.choices[0].message.content if completion.choices else ""
            return (content or "").strip()
        except Exception:
            return ""


app = FastAPI(title="HRM Chatbot Service", version="1.0.0")
engine = ChatEngine()
shared_secret = os.getenv("APP_SHARED_SECRET", "").strip()


@app.get("/health")
def health() -> Dict[str, Any]:
    return {
        "ok": True,
        "llm_enabled": bool(engine.client),
        "model": engine.model,
    }


@app.post("/chat", response_model=ChatResponse)
def chat(request: ChatRequest, x_app_secret: Optional[str] = Header(default=None)) -> ChatResponse:
    if shared_secret:
        if not x_app_secret or x_app_secret != shared_secret:
            raise HTTPException(status_code=401, detail="INVALID_SHARED_SECRET")

    try:
        return engine.answer(request)
    except mysql.connector.Error as exc:
        raise HTTPException(status_code=500, detail=f"DB_ERROR: {exc}") from exc
    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"CHATBOT_ERROR: {exc}") from exc
