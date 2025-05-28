<?php
/**
 * Template for SIM search form
 *
 * @package Nice_SIM
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nice-sim-container">
    <div class="nice-sim-form-container">
        <form id="nice-sim-form" class="form-horizontal" action="" method="post">
            <div class="form-group row">
                <label class="control-label col-md-4 col-5 font-weight-bold text-dark text-2"><?php esc_html_e('Nhập số điện thoại', 'nice-sim'); ?></label>
                <div class="col-md-8 col-7">
                    <div class="field-phone">
                        <input type="number" id="nice-sim-dien-thoai" class="form-control" name="dien_thoai" value="" min="1" placeholder="" required>
                        <div class="help-block"></div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-md-4 col-5 font-weight-bold text-dark text-2"><?php esc_html_e('Ngày sinh của bạn (Dương Lịch)', 'nice-sim'); ?></label>
                <div class="col-md-8 col-7">
                    <div class="field-birthday wrap-birthday-3">
                        <select id="nice-sim-ngay-sinh" class="form-control" name="ngay_sinh" required>
                            <?php for ($i = 1; $i <= 31; $i++) : ?>
                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                            <?php endfor; ?>
                        </select>
                        <select id="nice-sim-thang-sinh" class="form-control" name="thang_sinh" required>
                            <?php for ($i = 1; $i <= 12; $i++) : ?>
                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                            <?php endfor; ?>
                        </select>
                        <select id="nice-sim-nam-sinh" class="form-control" name="nam_sinh" required>
                            <?php for ($i = 1920; $i <= date('Y'); $i++) : ?>
                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-md-4 col-5 font-weight-bold text-dark text-2"><?php esc_html_e('Giới tính', 'nice-sim'); ?></label>
                <div class="col-md-8 col-7">
                    <div class="field-gender">
                        <select id="nice-sim-gioi-tinh" class="form-control" name="gioi_tinh" required>
                            <option value="Nam"><?php esc_html_e('Nam', 'nice-sim'); ?></option>
                            <option value="Nữ"><?php esc_html_e('Nữ', 'nice-sim'); ?></option>
                        </select>
                        <div class="help-block"></div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="control-label col-md-4 col-5 font-weight-bold text-dark text-2"><?php esc_html_e('Giờ sinh', 'nice-sim'); ?></label>
                <div class="col-md-8 col-7">
                    <div class="field-birth-hour">
                        <select id="nice-sim-gio-sinh" class="form-control" name="gio_sinh" required>
                            <option value="23 giờ đến 1 giờ"><?php esc_html_e('23 giờ đến 1 giờ', 'nice-sim'); ?></option>
                            <option value="1 giờ đến 3 giờ"><?php esc_html_e('1 giờ đến 3 giờ', 'nice-sim'); ?></option>
                            <option value="3 giờ đến 5 giờ"><?php esc_html_e('3 giờ đến 5 giờ', 'nice-sim'); ?></option>
                            <option value="5 giờ đến 7 giờ"><?php esc_html_e('5 giờ đến 7 giờ', 'nice-sim'); ?></option>
                            <option value="7 giờ đến 9 giờ"><?php esc_html_e('7 giờ đến 9 giờ', 'nice-sim'); ?></option>
                            <option value="9 giờ đến 11 giờ"><?php esc_html_e('9 giờ đến 11 giờ', 'nice-sim'); ?></option>
                            <option value="11 giờ đến 13 giờ"><?php esc_html_e('11 giờ đến 13 giờ', 'nice-sim'); ?></option>
                            <option value="13 giờ đến 15 giờ"><?php esc_html_e('13 giờ đến 15 giờ', 'nice-sim'); ?></option>
                            <option value="15 giờ đến 17 giờ"><?php esc_html_e('15 giờ đến 17 giờ', 'nice-sim'); ?></option>
                            <option value="17 giờ đến 19 giờ"><?php esc_html_e('17 giờ đến 19 giờ', 'nice-sim'); ?></option>
                            <option value="19 giờ đến 21 giờ"><?php esc_html_e('19 giờ đến 21 giờ', 'nice-sim'); ?></option>
                            <option value="21 giờ đến 23 giờ"><?php esc_html_e('21 giờ đến 23 giờ', 'nice-sim'); ?></option>
                        </select>
                        <div class="help-block"></div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary nice-sim-submit"><?php esc_html_e('Xem kết quả', 'nice-sim'); ?></button>
            </div>
        </form>
    </div>
    
    <div class="nice-sim-loading" style="display: none;">
        <div class="spinner"></div>
        <p><?php esc_html_e('Đang xử lý...', 'nice-sim'); ?></p>
    </div>
    
    <div class="nice-sim-result-container" style="display: none;">
        <h3 class="nice-sim-result-title"><?php esc_html_e('Kết quả phong thủy sim', 'nice-sim'); ?></h3>
        <div class="nice-sim-result-content"></div>
    </div>
    
    <div class="nice-sim-error" style="display: none;">
        <p class="nice-sim-error-message"></p>
    </div>
</div> 