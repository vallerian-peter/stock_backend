# Help Center → Google Sheets

The API always saves a support request in the database first. Google Sheets is
an optional secondary copy, so a temporary Google outage never loses the user's
request.

## Connect a sheet

1. Create a Google Sheet and copy its ID from the URL between `/d/` and `/edit`.
2. Open **Extensions → Apps Script** and paste
   `docs/google-sheets-support-webhook.gs`.
3. In Apps Script, open **Project Settings → Script properties** and add:
   - `SUPPORT_SHEET_ID`: the spreadsheet ID
   - `SUPPORT_SHEET_NAME`: for example `Support Requests`
   - `SUPPORT_WEBHOOK_SECRET`: a long random secret
4. Select **Deploy → New deployment → Web app**.
5. Set **Execute as** to yourself and choose the access level that lets the
   backend call the URL. Deploy and copy the `/exec` URL.
6. Add the matching values to `stock_backend/.env`:

   ```dotenv
   GOOGLE_SHEETS_SUPPORT_WEBHOOK_URL=https://script.google.com/macros/s/.../exec
   GOOGLE_SHEETS_SUPPORT_WEBHOOK_SECRET=the-same-long-random-secret
   ```

7. Run `php artisan config:clear`, submit a test request from Help Center, and
   verify the new row in the sheet.

Keep the webhook URL and secret server-side. Never expose either value through
the Next.js public environment.
