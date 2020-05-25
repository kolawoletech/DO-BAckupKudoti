<div class="wrap">
    <h1>UserGuiding</h1>
    <form action="options.php" method="post">
        <?php settings_fields('userguiding-options'); ?>
        <?php do_settings_sections('userguiding-options'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row">Code</th>
                <td><textarea name="userguiding_code" required="required" rows="8" cols="100" ><?php echo esc_attr(get_option('userguiding_code')); ?></textarea></td>
            </tr>

            <tr valign="top">
                <th scope="row">Show in Site</th>
                <td><input type="checkbox" name="userguiding_site" <?php echo (esc_attr(get_option('userguiding_site', '')) == 'on') ? 'checked="checked"' : ''; ?> /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Show in Admin</th>
                <td><input type="checkbox" name="userguiding_admin" <?php echo (esc_attr(get_option('userguiding_admin', '')) == 'on') ? 'checked="checked"' : ''; ?> /></td>
            </tr>

            <tr valign="top">
                <th scope="row">Show in Customizer</th>
                <td><input type="checkbox" name="userguiding_customizer" <?php echo (esc_attr(get_option('userguiding_customizer', '')) == 'on') ? 'checked="checked"' : ''; ?> /></td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
        </p>
    </form>
</div>