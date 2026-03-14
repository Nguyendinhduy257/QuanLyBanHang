<?php
// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

function qlbh_nhanvien_page() {
    // ==========================================
    // A. KẾT NỐI CƠ SỞ DỮ LIỆU
    // ==========================================
    $conn = new mysqli("localhost", "root", "", "QuanLyBanHang");
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        die("<div class='notice notice-error'><p>Kết nối CSDL thất bại.</p></div>");
    }

    // ==========================================
    // B. XỬ LÝ SỰ KIỆN: THÊM, SỬA, XÓA
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 1. Nút THÊM MỚI
        if (isset($_POST['btnThemMoi'])) {
            $h = $conn->real_escape_string($_POST['txtHoTen']);
            $n = $conn->real_escape_string($_POST['txtNgaySinh']);
            $g = $conn->real_escape_string($_POST['cbGioiTinh']);
            
            $sql = "INSERT INTO tblNhanVien (hoten, ngaysinh, gioitinh) VALUES ('$h', '$n', '$g')";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='notice notice-success is-dismissible'><p>Thêm nhân viên thành công!</p></div>";
            } else {
                echo "<div class='notice notice-error is-dismissible'><p>Lỗi: " . $conn->error . "</p></div>";
            }
        } 
        // 2. Nút SỬA
        elseif (isset($_POST['btnSua'])) {
            $m = $conn->real_escape_string($_POST['txtMaNV']);
            $h = $conn->real_escape_string($_POST['txtHoTen']);
            $n = $conn->real_escape_string($_POST['txtNgaySinh']);
            $g = $conn->real_escape_string($_POST['cbGioiTinh']);
            
            if (!empty($m)) {
                $sql = "UPDATE tblNhanVien SET hoten='$h', ngaysinh='$n', gioitinh='$g' WHERE MaNV='$m'";
                if ($conn->query($sql) === TRUE) {
                    echo "<div class='notice notice-success is-dismissible'><p>Đã cập nhật nhân viên!</p></div>";
                } else {
                    echo "<div class='notice notice-error is-dismissible'><p>Lỗi: " . $conn->error . "</p></div>";
                }
            } else {
                echo "<div class='notice notice-warning is-dismissible'><p>Vui lòng chọn 1 nhân viên ở bảng dưới để sửa!</p></div>";
            }
        }
        // 3. Nút XÓA
        elseif (isset($_POST['btnXoa'])) {
            if (!empty($_POST['chkXoa'])) {
                $ids = array_map(function($id) use ($conn) {
                    return "'" . $conn->real_escape_string($id) . "'";
                }, $_POST['chkXoa']);
                
                $id_list = implode(',', $ids);
                $sql = "DELETE FROM tblNhanVien WHERE MaNV IN ($id_list)";
                if ($conn->query($sql) === TRUE) {
                    echo "<div class='notice notice-success is-dismissible'><p>Đã xóa các nhân viên được chọn!</p></div>";
                } else {
                    echo "<div class='notice notice-error is-dismissible'><p>Lỗi: " . $conn->error . "</p></div>";
                }
            } else {
                echo "<div class='notice notice-warning is-dismissible'><p>Bạn chưa tích chọn nhân viên nào để xóa!</p></div>";
            }
        }
    }

    // ==========================================
    // C. GIAO DIỆN HIỂN THỊ
    // ==========================================
    ?>
    <div class="wrap" style="max-width: 900px;">
        <h2 style="text-align: center; font-weight: bold;">Cập nhật nhân viên</h2>
        <br>

        <form method="POST" action="?page=qlbh-nhan-vien">
            
            <table style="margin: 0 auto; width: 60%; font-size: 15px; border-spacing: 10px;">
                <tr>
                    <td style="text-align: right; width: 30%;"><strong>Mã nhân viên</strong></td>
                    <td>
                        <input type="text" name="txtMaNV" id="txtMaNV" readonly style="background: #e5e5e5; width: 100%;" placeholder="Tự động sinh mã">
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;"><strong>Họ tên</strong></td>
                    <td><input type="text" name="txtHoTen" id="txtHoTen" style="width: 100%;" required></td>
                </tr>
                <tr>
                    <td style="text-align: right;"><strong>Ngày sinh</strong></td>
                    <td><input type="text" name="txtNgaySinh" id="txtNgaySinh" style="width: 100%;" placeholder="dd/mm/yyyy" required></td>
                </tr>
                <tr>
                    <td style="text-align: right;"><strong>Giới tính</strong></td>
                    <td>
                        <select name="cbGioiTinh" id="cbGioiTinh" style="width: 50%;">
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                        </select>
                    </td>
                </tr>
            </table>

            <div style="text-align: center; margin: 25px 0; margin-left: 17%;">
                <input type="submit" name="btnThemMoi" class="button" value="Thêm mới" style="padding: 0 20px; margin-right: 15px; font-weight: bold;">
                <input type="submit" name="btnXoa" class="button" value="Xóa" style="padding: 0 30px; margin-right: 15px; font-weight: bold;" onclick="return confirm('Bạn có chắc chắn muốn xóa các nhân viên đã chọn?');">
                <input type="submit" name="btnSua" class="button" value="Sửa" style="padding: 0 30px; font-weight: bold;">
            </div>

            <table class="wp-list-table widefat fixed striped" style="background: #fff; margin-left: 8%;">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">Chọn xóa</th>
                        <th style="width: 80px; text-align: center;">Chọn sửa</th>
                        <th>Mã nhân viên</th>
                        <th>Họ tên</th>
                        <th>Ngày sinh</th>
                        <th>Giới tính</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_list = "SELECT * FROM tblNhanVien";
                    $result_list = $conn->query($sql_list);

                    if ($result_list->num_rows > 0) {
                        while($row = $result_list->fetch_assoc()) {
                            echo "<tr>";
                            
                            // Checkbox xóa
                            echo "<td style='text-align: center;'><input type='checkbox' name='chkXoa[]' value='".esc_attr($row['MaNV'])."'></td>";
                            
                            // Icon sửa: Thay thẻ a href thành sự kiện onclick gọi hàm JavaScript
                            echo "<td style='text-align: center;'>
                                    <a href='#' onclick=\"duaDuLieuLenForm('".esc_js($row['MaNV'])."', '".esc_js($row['hoten'])."', '".esc_js($row['ngaysinh'])."', '".esc_js($row['gioitinh'])."'); return false;\" title='Sửa'>
                                        <span class='dashicons dashicons-edit' style='color: #2271b1;'></span>
                                    </a>
                                  </td>";
                                  
                            echo "<td>" . esc_html($row['MaNV']) . "</td>";
                            echo "<td>" . esc_html($row['hoten']) . "</td>";
                            echo "<td>" . esc_html($row['ngaysinh']) . "</td>";
                            echo "<td>" . esc_html($row['gioitinh']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>Chưa có nhân viên nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </form>
    </div>

    <script>
    function duaDuLieuLenForm(maNV, hoTen, ngaySinh, gioiTinh) {
        document.getElementById('txtMaNV').value = maNV;
        document.getElementById('txtHoTen').value = hoTen;
        document.getElementById('txtNgaySinh').value = ngaySinh;
        document.getElementById('cbGioiTinh').value = gioiTinh;
        
        // Tùy chọn: Cuộn mượt mà lên trên cùng để tiện sửa
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    </script>
    <?php
    $conn->close();
}
?>