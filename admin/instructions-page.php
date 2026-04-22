<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function cftg_render_instructions_page() {
    ?>
    <div class="wrap cftg-admin cftg-instructions">
        <h1 class="cftg-admin-title">
            <img src="https://cftgroup.ca/wp-content/uploads/2024/09/cft-group-logo.png" alt="CFT Group" style="height:36px;vertical-align:middle;margin-right:10px">
            Setup Instructions
        </h1>

        <div class="cftg-instructions-grid">

            <!-- Sidebar TOC -->
            <aside class="cftg-toc">
                <h3>On this page</h3>
                <ul>
                    <li><a href="#step-1">1. Get GHL Credentials</a></li>
                    <li><a href="#step-2">2. Create Custom Fields in GHL</a></li>
                    <li><a href="#step-3">3. Add Field IDs to Plugin</a></li>
                    <li><a href="#step-4">4. Add Shortcodes to Pages</a></li>
                    <li><a href="#step-5">5. Test a Submission</a></li>
                    <li><a href="#custom-fields-ref">Custom Fields Reference</a></li>
                    <li><a href="#troubleshooting">Troubleshooting</a></li>
                </ul>
            </aside>

            <!-- Main content -->
            <main class="cftg-instructions-body">

                <!-- Step 1 -->
                <section id="step-1" class="cftg-instr-section">
                    <div class="cftg-instr-step-num">1</div>
                    <div class="cftg-instr-content">
                        <h2>Get Your GHL Credentials</h2>
                        <p>You need two things from GoHighLevel: your <strong>Location ID</strong> and a <strong>Private API Key</strong>.</p>
                        <ol>
                            <li>Log in to your GoHighLevel account and go to the sub-account (location) you want to connect.</li>
                            <li>Navigate to <strong>Settings → Business Profile</strong>.</li>
                            <li>Scroll down to find your <strong>Location ID</strong> — copy it.</li>
                            <li>Still in Settings, go to <strong>Integrations → Private Integrations</strong>.</li>
                            <li>Click <strong>+ Add Key</strong>, give it a name like "CFT Forms", and copy the generated API key.</li>
                        </ol>
                        <div class="cftg-instr-note">
                            <span class="dashicons dashicons-lock"></span>
                            <span>Keep your API key private. It provides full access to your GHL location.</span>
                        </div>
                        <p>Paste both values in the <a href="<?php echo admin_url( 'admin.php?page=cftg-settings&tab=connection' ); ?>">GHL Connection tab</a>, then click <strong>Test Connection</strong> to verify.</p>
                    </div>
                </section>

                <!-- Step 2 -->
                <section id="step-2" class="cftg-instr-section">
                    <div class="cftg-instr-step-num">2</div>
                    <div class="cftg-instr-content">
                        <h2>Create Custom Fields in GoHighLevel</h2>
                        <p>Go to <strong>Settings → Custom Fields → Contacts</strong> and create each field below. Use the exact <strong>Field Type</strong> listed for best results.</p>

                        <h3 class="cftg-cf-group-title"><span class="cftg-form-badge cftg-badge-bin">Bin Estimate</span></h3>
                        <table class="cftg-cf-table widefat">
                            <thead><tr><th>Field Name (Label)</th><th>Field Type</th><th>Notes</th></tr></thead>
                            <tbody>
                                <tr><td><strong>Dispose Types</strong></td><td>Text / Multi-line Text</td><td>Comma-separated list of items selected</td></tr>
                                <tr><td><strong>Bin Delivery Date</strong></td><td>Date</td><td>Requested delivery date</td></tr>
                                <tr><td><strong>Bin Rental Duration</strong></td><td>Text</td><td>One day, One week, etc.</td></tr>
                                <tr><td><strong>Bin Size</strong></td><td>Text</td><td>10 yard, 20 yard, 30 yard, 40 yard, Not sure</td></tr>
                            </tbody>
                        </table>

                        <h3 class="cftg-cf-group-title" style="margin-top:20px"><span class="cftg-form-badge cftg-badge-scrap">Scrap Metal</span></h3>
                        <table class="cftg-cf-table widefat">
                            <thead><tr><th>Field Name (Label)</th><th>Field Type</th><th>Notes</th></tr></thead>
                            <tbody>
                                <tr><td><strong>Scrap Types</strong></td><td>Text / Multi-line Text</td><td>Comma-separated list of materials</td></tr>
                            </tbody>
                        </table>

                        <h3 class="cftg-cf-group-title" style="margin-top:20px"><span class="cftg-form-badge cftg-badge-vehicle">Vehicle Quote</span></h3>
                        <table class="cftg-cf-table widefat">
                            <thead><tr><th>Field Name (Label)</th><th>Field Type</th><th>Notes</th></tr></thead>
                            <tbody>
                                <tr><td><strong>Vehicle Year</strong></td><td>Text</td><td>e.g. 2018</td></tr>
                                <tr><td><strong>Vehicle Make</strong></td><td>Text</td><td>e.g. Honda</td></tr>
                                <tr><td><strong>Vehicle Model</strong></td><td>Text</td><td>e.g. Civic</td></tr>
                                <tr><td><strong>Engine Running</strong></td><td>Radio / Text</td><td>Yes or No</td></tr>
                                <tr><td><strong>Parts Missing</strong></td><td>Radio / Text</td><td>Yes or No</td></tr>
                                <tr><td><strong>Missing Parts Description</strong></td><td>Text / Multi-line Text</td><td>Description of what is missing</td></tr>
                            </tbody>
                        </table>

                        <div class="cftg-instr-note" style="margin-top:16px">
                            <span class="dashicons dashicons-info-outline"></span>
                            <span>Standard GHL contact fields — <strong>First Name, Last Name, Email, Phone, Postal Code</strong> — are mapped automatically. You do not need to create custom fields for these.</span>
                        </div>
                    </div>
                </section>

                <!-- Step 3 -->
                <section id="step-3" class="cftg-instr-section">
                    <div class="cftg-instr-step-num">3</div>
                    <div class="cftg-instr-content">
                        <h2>Copy Field IDs into the Plugin</h2>
                        <ol>
                            <li>In GHL, go to <strong>Settings → Custom Fields → Contacts</strong>.</li>
                            <li>Click on a field you created.</li>
                            <li>The field's <strong>ID</strong> is displayed in the field detail panel (looks like <code>xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code>).</li>
                            <li>Copy that ID.</li>
                            <li>In this plugin, go to the <a href="<?php echo admin_url( 'admin.php?page=cftg-settings&tab=fields' ); ?>"><strong>Custom Fields tab</strong></a> and paste the ID into the matching input.</li>
                            <li>Repeat for each field. Click <strong>Save Settings</strong> when done.</li>
                        </ol>
                    </div>
                </section>

                <!-- Step 4 -->
                <section id="step-4" class="cftg-instr-section">
                    <div class="cftg-instr-step-num">4</div>
                    <div class="cftg-instr-content">
                        <h2>Add Shortcodes to Your Pages</h2>
                        <p>Each form has its own shortcode. Paste it into any WordPress page or post:</p>
                        <table class="cftg-cf-table widefat">
                            <thead><tr><th>Form</th><th>Shortcode</th></tr></thead>
                            <tbody>
                                <tr><td>Bin Dumpster Estimate</td><td><code>[cftg_bin_estimate]</code></td></tr>
                                <tr><td>Scrap Metal Estimate</td><td><code>[cftg_scrap_metal]</code></td></tr>
                                <tr><td>Vehicle Quote</td><td><code>[cftg_vehicle_quote]</code></td></tr>
                            </tbody>
                        </table>
                        <p style="margin-top:12px">If you use the Gutenberg editor, add a <strong>Shortcode block</strong>. In Elementor, use the <strong>Shortcode widget</strong>.</p>
                    </div>
                </section>

                <!-- Step 5 -->
                <section id="step-5" class="cftg-instr-section">
                    <div class="cftg-instr-step-num">5</div>
                    <div class="cftg-instr-content">
                        <h2>Test a Live Submission</h2>
                        <ol>
                            <li>Open a page that contains one of your form shortcodes.</li>
                            <li>Fill in all fields and submit the form.</li>
                            <li>In GHL, go to <strong>Contacts</strong> and search for the email address you used.</li>
                            <li>Confirm the contact was created with the correct custom field values and the appropriate tag (e.g. <code>CFT - Bin Estimate</code>).</li>
                        </ol>
                        <div class="cftg-instr-note cftg-note-success">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <span>If everything is set up correctly you should see the contact in GHL within seconds of submitting.</span>
                        </div>
                    </div>
                </section>

                <!-- Troubleshooting -->
                <section id="troubleshooting" class="cftg-instr-section">
                    <div class="cftg-instr-step-num" style="background:#6b7280">?</div>
                    <div class="cftg-instr-content">
                        <h2>Troubleshooting</h2>
                        <table class="cftg-cf-table widefat">
                            <thead><tr><th>Problem</th><th>Solution</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td><strong>Test Connection fails</strong></td>
                                    <td>Check that your API key and Location ID are correct. Make sure the API key hasn't expired or been revoked in GHL.</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact is created but custom fields are empty</strong></td>
                                    <td>Verify the field IDs in the Custom Fields tab match the IDs in GHL exactly.</td>
                                </tr>
                                <tr>
                                    <td><strong>Form submits but nothing shows in GHL</strong></td>
                                    <td>Check that the GHL API key has permission to create contacts. Also check your server PHP error log for any curl/http errors.</td>
                                </tr>
                                <tr>
                                    <td><strong>Shortcode shows raw text <code>[cftg_...]</code></strong></td>
                                    <td>Make sure the plugin is activated. Go to Plugins → Installed Plugins and confirm CFT Group Forms is active.</td>
                                </tr>
                                <tr>
                                    <td><strong>Form styles look broken</strong></td>
                                    <td>Some themes add conflicting CSS. Add <code>max-width: none; box-sizing: border-box;</code> to the page's custom CSS. Contact your developer if the issue persists.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

            </main>
        </div>
    </div>
    <?php
}
