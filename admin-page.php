<div class="wrap">
    <h1>Custom Coupon Generator</h1>
    <form id="coupon-generator-form">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="discount">Discount Percentage</label></th>
                <td><input type="number" id="discount" name="discount" min="1" max="100" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="count">Number of Coupons</label></th>
                <td><input type="number" id="count" name="count" min="1" max="1000" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="expiry_date">Expiry Date</label></th>
                <td><input type="date" id="expiry_date" name="expiry_date" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="usage_limit">Usage Limit per Coupon</label></th>
                <td><input type="number" id="usage_limit" name="usage_limit" min="1" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="usage_limit_per_user">Usage Limit per User</label></th>
                <td><input type="number" id="usage_limit_per_user" name="usage_limit_per_user" min="1" required></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Generate Coupons">
        </p>
    </form>
    <div id="coupon-generator-result"></div>
    <div id="coupon-generator-log"></div>
</div>
