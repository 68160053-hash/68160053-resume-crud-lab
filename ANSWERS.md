# Answers: Resume CRUD Lab (68160053)

## Task 1-3: SQL ที่เติม (ทุกคำสั่งใช้ prepared statement)

| Task | ไฟล์ | SQL | bind |
|---|---|---|---|
| 1 INSERT | `skill_create.php` | `INSERT INTO skill (profile_id, skill_name, skill_level) VALUES (?, ?, ?)` | `iss` = profile_id, name, level |
| 2a SELECT | `skill_edit.php` | `SELECT skill_name, skill_level FROM skill WHERE id = ? AND profile_id = ?` | `ii` = id, profile_id |
| 2b UPDATE | `skill_edit.php` | `UPDATE skill SET skill_name = ?, skill_level = ? WHERE id = ? AND profile_id = ?` | `ssii` = name, level, id, profile_id |
| 3 DELETE | `skill_delete.php` | `DELETE FROM skill WHERE id = ? AND profile_id = ?` | `ii` = id, profile_id |

## Task 4: ทำไม UPDATE และ DELETE ต้องมี WHERE

`UPDATE` / `DELETE` ที่ไม่มี `WHERE` จะมีผลกับ **ทุกแถว** ในตาราง

```sql
DELETE FROM skill;                          -- ลบทุก skill ของทุกคน
UPDATE skill SET skill_level = 'Beginner';  -- เปลี่ยนระดับของทุกแถว
```

เงื่อนไข `WHERE id = ? AND profile_id = ?` ทำให้คำสั่งแตะได้ **แค่ 1 แถว** ที่เป็นของ profile นี้เท่านั้น
`id` ระบุแถว และ `profile_id` กันไม่ให้แก้หรือลบแถวของ profile อื่น แม้ใครจะส่ง id ของคนอื่นมา

## Task 4: การทำ parameter binding

เทียบการต่อสตริงกับ prepared statement สมมติผู้โจมตีส่ง `id = "1 OR 1=1"`

```php
// ไม่ปลอดภัย: ต่อค่าจากผู้ใช้เข้า SQL ตรง ๆ
$sql = "DELETE FROM skill WHERE id = " . $_GET['id'];
// ได้ SQL จริง: DELETE FROM skill WHERE id = 1 OR 1=1   -> ลบทุกแถว

// ปลอดภัย: ใช้ placeholder + bind (ที่ใช้ใน skill_delete.php)
$stmt = mysqli_prepare($conn, 'DELETE FROM skill WHERE id = ? AND profile_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $id, $pid);
mysqli_stmt_execute($stmt);
// ค่าถูกส่งแยกจากตัวคำสั่ง MySQL มองเป็น "ข้อมูล" เสมอ ไม่ใช่ "โค้ด SQL"
```

ชั้นป้องกันอื่นในโปรเจกต์: `valid_id()` ตรวจว่าเป็นจำนวนเต็ม >= 1, ระดับ skill ต้องอยู่ในรายการที่อนุญาต
(`in_array(..., true)`), การเปลี่ยนข้อมูลใช้ POST + CSRF token, และใช้ `h()` (htmlspecialchars) escape ตอนแสดงผล

## Task 5: Git workflow

- `main` = starter ของอาจารย์ที่เอา `.env` และ `secret/` ออกแล้ว
- ทำงานบน branch `feature/complete-crud-sql` แล้วรวมกลับ `main`
- commit แยกตามงาน: ความลับ/`.gitignore`, การตั้งค่า DB, INSERT, SELECT+UPDATE, DELETE, เก็บกวาด, ธีม UI, เอกสาร
- **ไม่มีความลับใน Git**: `.gitignore` กัน `.env`, `.env.*` และ `secret/*` มีเฉพาะ `.env.example` กับ
  `secret/config.example.php` ที่เป็นค่าตัวอย่าง รหัสผ่านจริงอยู่ในไฟล์ `secret/config.php` บนเซิร์ฟเวอร์เท่านั้น

## ข้อจำกัดที่ทราบ (Security)

- หน้า CRUD ยัง**ไม่มีระบบล็อกอิน** ตามที่ README ของอาจารย์ระบุว่าเป็นตัวอย่างในห้องเรียน
  ถ้านำขึ้นเว็บจริงต้องเพิ่มการยืนยันตัวตน/สิทธิ์ก่อน
- ห้าม import `database/setup.sql` ลงฐานที่มีข้อมูลจริง เพราะมี `DROP TABLE` ของ `profile`, `education`, `skill`, `training`
