<?php
/*
Plugin Name: Quản Lý Bán Hàng
Description: Plugin quản lý bán hàng - Kết nối CSDL ngoài QuanLyBanHang.
Version: 1.0
Author: Sinh Viên
*/

// Ngăn chặn truy cập trực tiếp vào file
if (!defined('ABSPATH')) {
    exit;
}

// 1. Tạo Menu trong trang quản trị WordPress
add_action('admin_menu', 'qlbh_add_admin_menu');
// 1. Tạo Menu trong trang quản trị WordPress
add_action('admin_menu', 'qlbh_add_admin_menu');
function qlbh_add_admin_menu()
{
    // Menu chính (Cha)
    add_menu_page('Quản Lý Bán Hàng', 'QL Bán Hàng', 'manage_options', 'quan-ly-ban-hang', 'qlbh_loaisanpham_page', 'dashicons-cart', 99);

    // Menu con 1: Loại Sản Phẩm (Gắn cùng slug với menu cha để nó là trang mặc định)
    add_submenu_page('quan-ly-ban-hang', 'Loại Sản Phẩm', 'Loại Sản Phẩm', 'manage_options', 'quan-ly-ban-hang', 'qlbh_loaisanpham_page',12);

    // Menu con 2: Nhân Viên (Trang mới)
    add_submenu_page('quan-ly-ban-hang', 'Cập nhật Nhân Viên', 'Nhân Viên', 'manage_options', 'qlbh-nhan-vien', 'qlbh_nhanvien_page');
}

// Gọi file nhanvien.php để chạy giao diện Nhân viên
require_once plugin_dir_path(__FILE__) . 'nhanvien.php';
// 2. Hàm xử lý giao diện và CSDL cho trang Loại Sản Phẩm
// 2. Hàm xử lý giao diện và CSDL cho trang Loại Sản Phẩm
function qlbh_loaisanpham_page()
{
    // ==========================================
    // A. KẾT NỐI CƠ SỞ DỮ LIỆU (MySQL)
    // ==========================================
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "QuanLyBanHang";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        die("<div class='notice notice-error'><p>Kết nối CSDL thất bại: " . $conn->connect_error . "</p></div>");
    }

    // Biến lưu trữ dữ liệu để đưa lên Form (Dùng cho Cập nhật)
    $maLoai = '';
    $tenLoai = '';
    $is_edit = false;
    $old_maLoai = '';

    // ==========================================
    // B. XỬ LÝ SỰ KIỆN: LẤY DỮ LIỆU CŨ ĐỂ SỬA
    // ==========================================
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $is_edit = true;
        $edit_id = $conn->real_escape_string($_GET['id']);

        $sql_get = "SELECT * FROM tblLoaiSanPham WHERE MaLoai = '$edit_id'";
        $result = $conn->query($sql_get);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $maLoai = $row['MaLoai'];
            $tenLoai = $row['TenLoai'];
            $old_maLoai = $row['MaLoai']; // Lưu lại mã cũ để đối chiếu khi update
        }
    }

    // ==========================================
    // C. XỬ LÝ SỰ KIỆN: THÊM MỚI & CẬP NHẬT
    // ==========================================
    // 1. Nếu bấm nút Thêm Mới
    if (isset($_POST['btnThemMoi'])) {
        $m = $conn->real_escape_string($_POST['txtMaLoai']);
        $t = $conn->real_escape_string($_POST['txtTenLoai']);
        if (!empty($m) && !empty($t)) {
            $sql = "INSERT INTO tblLoaiSanPham (MaLoai, TenLoai) VALUES ('$m', '$t')";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='notice notice-success is-dismissible' style='margin-left: 12.5%; width:120%'><p>Đã thêm mới thành công!</p></div>";
            } else {
                echo "<div class='notice notice-error is-dismissible' style='margin-left:12.5%; width:120%'><p>Lỗi thêm mới: " . $conn->error . "</p></div>";
            }
        }
    }

    // 2. Nếu bấm nút Cập Nhật
    if (isset($_POST['btnCapNhat'])) {
        $m_new = $conn->real_escape_string($_POST['txtMaLoai']);
        $t_new = $conn->real_escape_string($_POST['txtTenLoai']);
        $m_old = $conn->real_escape_string($_POST['oldMaLoai']); // Lấy mã cũ từ thẻ hidden

        if (!empty($m_new) && !empty($t_new)) {
            $sql_update = "UPDATE tblLoaiSanPham SET MaLoai = '$m_new', TenLoai = '$t_new' WHERE MaLoai = '$m_old'";
            if ($conn->query($sql_update) === TRUE) {
                echo "<div class='notice notice-success is-dismissible' style='margin-left: 12.5%;width:113.3%'><p>Đã cập nhật thành công!</p></div>";
                // Reset lại form sau khi cập nhật
                $maLoai = '';
                $tenLoai = '';
                $is_edit = false;
            } else {
                echo "<div class='notice notice-error is-dismissible'style='margin-left: 12.5%;width:113.5%%'><p>Lỗi cập nhật: " . $conn->error . "</p></div>";
            }
        }
    }

    // ==========================================
    // D. THIẾT KẾ GIAO DIỆN (FORM & BẢNG)
    // ==========================================
    ?>
    <div class="wrap" style="max-width: 800px;">
        <h2>Cập Nhật Loại Sản Phẩm</h2>
        <br>

        <form method="POST" action="?page=quan-ly-ban-hang">
            <input type="hidden" name="oldMaLoai" value="<?php echo esc_attr($old_maLoai); ?>">

            <table class="form-table"
                style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px;display: flex; justify-content: center; align-items: center; width: 120%; margin-left: 12.5%;">
                <tr>
                    <th scope="row" style="width: 150px; padding-left: 40px;"><label for="txtMaLoai">Mã loại sản
                            phẩm:</label></th>
                    <td style="padding-left: 20px;">
                        <input name="txtMaLoai" type="text" id="txtMaLoai" class="regular-text"
                            value="<?php echo esc_attr($maLoai); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="txtTenLoai" style="padding-left: 40px;">Tên loại sản phẩm:</label></th>
                    <td style="padding-left: 20px;">
                        <input name="txtTenLoai" type="text" id="txtTenLoai" class="regular-text"
                            value="<?php echo esc_attr($tenLoai); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td style="padding-left: 20px;">
                        <?php if ($is_edit): ?>
                            <input type="submit" name="btnCapNhat" id="btnCapNhat" class="button button-primary"
                                value="Cập nhật">
                            <a href="?page=quan-ly-ban-hang" class="button button-secondary">Hủy</a>
                        <?php else: ?>
                            <input type="submit" name="btnThemMoi" id="btnThemMoi" class="button button-primary"
                                value="Thêm mới" style="margin-left:25%;">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </form>

        <table class="wp-list-table widefat fixed striped" style="width:120%; margin-left: 12.5%;">
            <thead>
                <tr>
                    <th style="width: 150px;">Mã Loại</th>
                    <th>Tên Loại</th>
                    <th style="width: 100px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_list = "SELECT * FROM tblLoaiSanPham";
                $result_list = $conn->query($sql_list);

                if ($result_list->num_rows > 0) {
                    while ($row = $result_list->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>" . esc_html($row['MaLoai']) . "</strong></td>";
                        echo "<td>" . esc_html($row['TenLoai']) . "</td>";
                        // Nút Sửa sẽ truyền 'action=edit' và 'id' lên URL
                        echo "<td><a href='?page=quan-ly-ban-hang&action=edit&id=" . esc_attr($row['MaLoai']) . "' class='button button-small'>Sửa</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Chưa có dữ liệu nào trong bảng.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php

    $conn->close();
}
?>