<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Ensure the user has the appropriate capability to access this page
if (!current_user_can('manage_options')) {
    wp_die(__('Sorry, you are not allowed to access this page.', 'wc-emi-calculator'));
}

// Get saved banks and payment plans
$banks = get_option('wc_emi_banks', []);
$payment_plans = get_option('wc_emi_payment_plans', []);

// Handle editing bank
if (isset($_GET['edit_bank'])) {
    $bank_index = intval($_GET['edit_bank']);
    $editing_bank = $banks[$bank_index] ?? null;
}

// Handle editing payment plan
if (isset($_GET['edit_payment_plan'])) {
    $plan_index = intval($_GET['edit_payment_plan']);
    $editing_plan = $payment_plans[$plan_index] ?? null;
}

// Display admin page content
?>
<div class="wrap">
    <h1><?php esc_html_e('EMI Settings', 'wc-emi-calculator'); ?></h1>
    <div id="emi-admin">
        <div class="tabs">
            <button class="tablinks" data-tab="Banks" id="defaultTab"><?php esc_html_e('Banks', 'wc-emi-calculator'); ?></button>
            <button class="tablinks" data-tab="PaymentPlans"><?php esc_html_e('Payment Plans', 'wc-emi-calculator'); ?></button>
            <button class="tablinks" data-tab="CommercialBankIPG"><?php esc_html_e('Commercial Bank', 'wc-emi-calculator'); ?></button>
            <button class="tablinks" data-tab="SampathBankIPG"><?php esc_html_e('Sampath Bank', 'wc-emi-calculator'); ?></button>
        </div>

        <!-- Banks Tab -->
        <div id="Banks" class="tabcontent">
            <h2><?php esc_html_e('Manage Banks', 'wc-emi-calculator'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                
                <div class="form-group">
                <input type="hidden" name="action" value="save_bank">
                <?php wp_nonce_field('save_bank_action', 'save_bank_nonce'); ?>
                </div>

                <input type="hidden" name="bank_index" value="<?php echo esc_attr($_GET['edit_bank'] ?? ''); ?>">

                <div class="form-group">
                <label for="bank_name"><?php esc_html_e('Bank Name:', 'wc-emi-calculator'); ?></label>
                <input type="text" name="bank_name" id="bank_name" value="<?php echo esc_attr($editing_bank['name'] ?? ''); ?>" required><br>
                </div>

                <div class="form-group">
                <label for="bank_logo"><?php esc_html_e('Bank Logo:', 'wc-emi-calculator'); ?></label>
                    <div>
                        <input type="hidden" name="bank_logo" id="bank_logo" value="<?php echo esc_url($editing_bank['logo'] ?? ''); ?>">
                        <button type="button" id="upload_logo_button" class="button"><?php esc_html_e('Upload Image', 'wc-emi-calculator'); ?></button>
                        <div id="logo_preview" style="margin-top: 10px;">
                            <img src="<?php echo esc_url($editing_bank['logo'] ?? ''); ?>" alt="" style="max-width: 150px; max-height: 150px; <?php echo empty($editing_bank['logo']) ? 'display: none;' : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bank_note"><?php esc_html_e('Note (Optional):', 'wc-emi-calculator'); ?></label>
                    <?php
                    $content = $editing_bank['note'] ?? ''; // Load existing note or empty
                    $editor_id = 'bank_note'; // ID for the editor

                    $settings = array(
                        'textarea_name' => 'bank_note', // Name for form submission
                        'media_buttons' => true, // Show media upload button
                        'editor_height' => 200,
                        'quicktags' => true, // Enable quicktags (HTML mode)
                        'tinymce' => array(
                            'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                            'toolbar2' => '', // Remove extra toolbar
                        ),
                    );

                    wp_editor($content, $editor_id, $settings);
                    ?>
                </div>

                <button type="submit" class="button button-primary">
                    <?php echo isset($_GET['edit_bank']) ? esc_html__('Update Bank', 'wc-emi-calculator') : esc_html__('Save Bank', 'wc-emi-calculator'); ?>
                </button>
            </form>

            <h3><?php esc_html_e('Existing Banks', 'wc-emi-calculator'); ?></h3>
            <?php if (!empty($banks)) : ?>
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Name', 'wc-emi-calculator'); ?></th>
                            <th><?php esc_html_e('Logo', 'wc-emi-calculator'); ?></th>
                            <th><?php esc_html_e('Note', 'wc-emi-calculator'); ?></th>
                            <th><?php esc_html_e('Actions', 'wc-emi-calculator'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banks as $index => $bank) : ?>
                            <tr>
                                <td><?php echo esc_html($bank['name']); ?></td>
                                <td>
                                    <img src="<?php echo esc_url($bank['logo']); ?>" alt="<?php echo esc_attr($bank['name']); ?>" style="width: 50px; height: auto;">
                                </td>
                                <td><?php echo esc_html($bank['note']); ?></td>
                                <td>
                                    <button class="button open-edit-modal" data-index="<?php echo esc_attr($index); ?>">
                                        <?php esc_html_e('Edit', 'wc-emi-calculator'); ?>
                                    </button>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_bank">
                                        <input type="hidden" name="bank_index" value="<?php echo esc_attr($index); ?>">
                                        <?php wp_nonce_field('delete_bank_action', 'delete_bank_nonce'); ?>
                                        <button type="submit" class="button button-danger"><?php esc_html_e('Delete', 'wc-emi-calculator'); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('No banks available.', 'wc-emi-calculator'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Modal for Editing Bank -->
        <div id="editBankModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2><?php esc_html_e('Edit Bank', 'wc-emi-calculator'); ?></h2>
                <form id="editBankForm" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="save_bank">
                    <?php wp_nonce_field('save_bank_action', 'save_bank_nonce'); ?>
                    <input type="hidden" name="bank_index" id="bank_index">

                    <div class="form-group">
                    <label for="bank_name"><?php esc_html_e('Bank Name:', 'wc-emi-calculator'); ?></label>
                    <input type="text" name="bank_name" id="modal_bank_name" required>
                    </div>

                    <div class="form-group">
                    <label for="bank_logo"><?php esc_html_e('Bank Logo:', 'wc-emi-calculator'); ?></label>
                        <div>
                            <input type="hidden" name="bank_logo" id="modal_bank_logo">
                            <button type="button" id="modal_upload_logo_button" class="button"><?php esc_html_e('Upload Image', 'wc-emi-calculator'); ?></button>
                            <div id="modal_logo_preview" style="margin-top: 10px;">
                                <img id="modal_logo_img" src="" alt="" style="max-width: 150px; max-height: 150px; display: none;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                    <label for="bank_note"><?php esc_html_e('Note (Optional):', 'wc-emi-calculator'); ?></label>
                    <textarea name="bank_note" id="modal_bank_note"></textarea><br>
                    </div>
                    
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Changes', 'wc-emi-calculator'); ?></button>
                </form>
            </div>
        </div>


        <!-- Payment Plans Tab -->
        <div id="PaymentPlans" class="tabcontent" style="display: none;">
            <h2><?php esc_html_e('Manage Payment Plans', 'wc-emi-calculator'); ?></h2>
                <div class="form-container">
                    <form method="post" action="http://seetha-holdings.local/wp-admin/admin-post.php?tab=PaymentPlans">
                        <input type="hidden" name="action" value="save_payment_plan">
                        <input type="hidden" id="save_payment_plan_nonce" name="save_payment_plan_nonce" value="f76fe08683">
                        <input type="hidden" name="_wp_http_referer" value="/wp-admin/admin.php?page=wc-emi-settings">
                        <input type="hidden" name="plan_index" value="">

                        <div class="form-group">
                            <label for="bank_id">Select Bank:</label>
                            <select name="bank_id" id="modal_bank_id" required>
                                <option value=""><?php esc_html_e('Select a Bank', 'wc-emi-calculator'); ?></option>
                                <?php
                                $banks = get_option('wc_emi_banks', []);
                                foreach ($banks as $bank_id => $bank_data) {
                                    echo "<option value='" . esc_attr($bank_id) . "'>" . esc_html($bank_data['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="plan_name">Plan Name:</label>
                            <input type="text" name="plan_name" id="plan_name" required>
                        </div>

                        <div class="form-group">
                            <label for="duration">Duration (Months):</label>
                            <input type="number" name="duration" id="duration" required>
                        </div>

                        <div class="form-group">
                            <label>Convenience Fee Type:</label>
                            <div>
                                <input type="radio" name="convenience_fee_type" value="percentage" id="fee_percentage">
                                <label for="fee_percentage">Percentage</label>
                                <input type="radio" name="convenience_fee_type" value="fixed" id="fee_fixed">
                                <label for="fee_fixed">Fixed Amount</label>
                            </div>
                        </div>

                        <div id="percentage_fee_field" class="form-group" style="display: none;">
                            <label for="percentage">Convenience Fee (%):</label>
                            <input type="number" step="0.01" name="percentage">
                        </div>

                        <div id="fixed_fee_field" class="form-group" style="display: none;">
                            <label for="fee_fixed">Convenience Fee (Fixed Amount):</label>
                            <input type="number" step="0.01" name="fee_fixed">
                        </div>

                        <div class="form-group">
                            <label for="plan_start_date">Start Date:</label>
                            <input type="date" name="plan_start_date" id="plan_start_date" required>
                        </div>

                        <div class="form-group">
                            <label for="plan_end_date">End Date:</label>
                            <input type="date" name="plan_end_date" id="plan_end_date" required>
                        </div>

                        <button type="submit">Save Payment Plan</button>
                    </form>
                </div>

                <h3><?php esc_html_e('Existing Payment Plans', 'wc-emi-calculator'); ?></h3>
                            <?php if (!empty($payment_plans)) : ?>
                                <table class="wp-list-table widefat fixed striped table-view-list">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Bank', 'wc-emi-calculator'); ?></th>
                                            <th><?php esc_html_e('Plan Name', 'wc-emi-calculator'); ?></th>
                                            <th><?php esc_html_e('Duration (Months)', 'wc-emi-calculator'); ?></th>
                                            <th><?php esc_html_e('Convenience Rate (%)', 'wc-emi-calculator'); ?></th>
                                            <th><?php esc_html_e('Convenience Fixed Fee', 'wc-emi-calculator'); ?></th>
                                            <th><?php esc_html_e('Start Date', 'wc-emi-calculator'); ?></th>
                                            <th><?php esc_html_e('End Date', 'wc-emi-calculator'); ?></th>
                                            <th><?php esc_html_e('Actions', 'wc-emi-calculator'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payment_plans as $index => $plan) : ?>
                                            <tr>
                                                <td><?php echo esc_html($banks[$plan['bank_id']]['name'] ?? esc_html__('Unknown Bank', 'wc-emi-calculator')); ?></td>
                                                <td><?php echo esc_html($plan['plan_name'] ?? esc_html__('Unnamed Plan', 'wc-emi-calculator')); ?></td>
                                                <td><?php echo esc_html($plan['duration']); ?></td>
                                                <td><?php echo esc_html(!empty($plan['percentage']) ? $plan['percentage'] : 'Fixed fee added'); ?></td>
                                                <td><?php echo esc_html(!empty($plan['fee_fixed']) ? $plan['fee_fixed'] : 'Convenience Rate added'); ?></td>
                                                <td><?php echo esc_html($plan['start_date']); ?></td>
                                                <td><?php echo esc_html($plan['end_date']); ?></td>
                                                <td>
                                                    <button class="button open-edit-plan-modal" data-index="<?php echo esc_attr($index); ?>">
                                                        <?php esc_html_e('Edit', 'wc-emi-calculator'); ?>
                                                    </button>
                                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete_payment_plan">
                                                        <input type="hidden" name="plan_index" value="<?php echo esc_attr($index); ?>">
                                                        <?php wp_nonce_field('delete_plan_action', 'delete_plan_nonce'); ?>
                                                        <button type="submit" class="button button-danger"><?php esc_html_e('Delete', 'wc-emi-calculator'); ?></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else : ?>
                                <p><?php esc_html_e('No payment plans available.', 'wc-emi-calculator'); ?></p>
                            <?php endif; ?>
        </div>
        <div id="CommercialBankIPG" class="tabcontent" style="display: none;">
            <h2><?php esc_html_e('Commercial Bank IPG', 'wc-emi-calculator'); ?></h2>
           <p>Coming Soon</p>
        </div>
        <div id="SampathBankIPG" class="tabcontent" style="display: none;">
            <h2><?php esc_html_e('Sampath Bank IPG', 'wc-emi-calculator'); ?></h2>
            <p>Coming Soon</p>
        </div>

<script>
    jQuery(document).ready(function($) {
    var mediaUploader;

    $('#modal_upload_logo_button').on('click', function(e) {
        e.preventDefault();

        // If the uploader exists, reopen it
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create the media uploader
        mediaUploader = wp.media({
            title: 'Select Bank Logo',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        // When an image is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#modal_bank_logo').val(attachment.url); // Store URL in hidden input
            $('#modal_logo_img').attr('src', attachment.url).show(); // Show preview
        });

        // Open the uploader
        mediaUploader.open();
    });
});

</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function toggleFeeFields() {
        let selectedType = document.querySelector('input[name="convenience_fee_type_modal"]:checked');
        
        document.getElementById('percentage_fee_field_modal').style.display = (selectedType && selectedType.value === 'percentage_modal') ? 'flex' : 'none';
        document.getElementById('fixed_fee_field_modal').style.display = (selectedType && selectedType.value === 'fixed_modal') ? 'flex' : 'none';
    }

    // Ensure the correct radio buttons are targeted
    document.querySelectorAll('input[name="convenience_fee_type_modal"]').forEach(el => {
        el.addEventListener('change', toggleFeeFields);
    });

    toggleFeeFields(); // Initialize on page load
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Media uploader
        let mediaUploader;
        const uploadButton = document.getElementById('upload_logo_button');
        const logoInput = document.getElementById('bank_logo');
        const logoPreview = document.getElementById('logo_preview');
        const modal = document.getElementById('editBankModal');
        const closeBtn = modal.querySelector('.close');
        const editButtons = document.querySelectorAll('.open-edit-modal');
        const form = modal.querySelector('#editBankForm');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const index = this.getAttribute('data-index');
                const bank = <?php echo json_encode($banks); ?>[index];

                form.bank_index.value = index;
                form.modal_bank_name.value = bank.name;
                form.modal_bank_logo.value = bank.logo;
                form.modal_bank_note.value = bank.note;

                const img = document.getElementById('modal_logo_preview').querySelector('img');
                img.src = bank.logo;
                img.style.display = bank.logo ? 'block' : 'none';

                modal.style.display = 'block';
            });
        });

        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        uploadButton.addEventListener('click', function (e) {
            e.preventDefault();
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            mediaUploader = wp.media({
                title: '<?php esc_html_e("Select Bank Logo", "wc-emi-calculator"); ?>',
                button: {
                    text: '<?php esc_html_e("Use This Image", "wc-emi-calculator"); ?>'
                },
                multiple: false
            });
            mediaUploader.on('select', function () {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                logoInput.value = attachment.url;
                const img = logoPreview.querySelector('img');
                img.src = attachment.url;
                img.style.display = 'block';
            });
            mediaUploader.open();
        });

        // Tabs functionality
        const tablinks = document.querySelectorAll('.tablinks');
        const tabcontents = document.querySelectorAll('.tabcontent');

        tablinks.forEach(function (tablink) {
            tablink.addEventListener('click', function () {
                tabcontents.forEach(function (content) {
                    content.style.display = 'none';
                });
                tablinks.forEach(function (link) {
                    link.classList.remove('active');
                });
                document.getElementById(this.getAttribute('data-tab')).style.display = 'block';
                this.classList.add('active');
            });
        });

        // Set default tab
        document.getElementById('defaultTab').click();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editPlanModal');
    const closeBtn = modal.querySelector('.close');
    const editButtons = document.querySelectorAll('.open-edit-plan-modal');
    const form = modal.querySelector('#editPlanForm');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const index = this.getAttribute('data-index'); // Get plan index from button attribute
            const planData = <?php echo json_encode(get_option('wc_emi_payment_plans', [])); ?>;

            if (index !== null && index !== "") {
                const plan = planData[index];

                form.plan_index.value = index;  // Set plan index
                form.modal_bank_id.value = plan.bank_id;
                form.modal_plan_name.value = plan.plan_name;
                form.duration.value = plan.duration;
                form.percentage.value = plan.percentage;
                form.fee_fixed.value = plan.fee_fixed;
                form.modal_start_date.value = plan.start_date;
                form.modal_end_date.value = plan.end_date;
            } else {
                form.plan_index.value = ""; // Ensure it's empty for new plans
                form.modal_bank_id.value = "";
                form.modal_plan_name.value = "";
                form.duration.value = "";
                form.percentage.value = "";
                form.fee_fixed.value = "";
                form.modal_start_date.value = "";
                form.modal_end_date.value = "";
            }

            modal.style.display = 'block';
        });
    });
   
    function toggleFeeFields() {
        let selectedType = document.querySelector('input[name="convenience_fee_type"]:checked');
        document.getElementById('percentage_fee_field').style.display = (selectedType && selectedType.value === 'percentage') ? 'flex' : 'none';
        document.getElementById('fixed_fee_field').style.display = (selectedType && selectedType.value === 'fixed') ? 'flex' : 'none';
    }
    document.querySelectorAll('input[name="convenience_fee_type"]').forEach(el => {
        el.addEventListener('change', toggleFeeFields);
    });
    toggleFeeFields(); // Initialize on page load


    closeBtn.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Ensure form submission includes the correct plan_index
    form.addEventListener('submit', function () {
        if (!form.plan_index.value) {
            form.plan_index.value = ""; // Ensure empty string instead of null
        }
    });
});

</script>


<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 50%;
        border-radius: 8px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>
