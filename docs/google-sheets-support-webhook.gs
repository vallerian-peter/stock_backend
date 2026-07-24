function doPost(event) {
  try {
    const payload = JSON.parse(event.postData.contents);
    const properties = PropertiesService.getScriptProperties();
    const expectedSecret = properties.getProperty("SUPPORT_WEBHOOK_SECRET");

    if (!expectedSecret || payload.secret !== expectedSecret) {
      return jsonResponse({ ok: false, error: "Unauthorized" });
    }

    const spreadsheetId = properties.getProperty("SUPPORT_SHEET_ID");
    const sheetName =
      properties.getProperty("SUPPORT_SHEET_NAME") || "Support Requests";
    const spreadsheet = SpreadsheetApp.openById(spreadsheetId);
    const sheet =
      spreadsheet.getSheetByName(sheetName) ||
      spreadsheet.insertSheet(sheetName);

    ensureHeader(sheet);
    sheet.appendRow([
      safeCell(payload.referenceNumber),
      safeCell(payload.submittedAt),
      safeCell(payload.type),
      safeCell(payload.category),
      safeCell(payload.subject),
      safeCell(payload.message),
      safeCell(payload.priority),
      safeCell(payload.contactPreference),
      payload.rating || "",
      safeCell(payload.status),
      safeCell(payload.locale),
      safeCell(payload.sourcePath),
      safeCell(payload.user?.id),
      safeCell(payload.user?.firstName),
      safeCell(payload.user?.lastName),
      safeCell(payload.user?.email),
      safeCell(payload.user?.phone),
      safeCell(payload.user?.role),
    ]);

    return jsonResponse({ ok: true });
  } catch (error) {
    return jsonResponse({ ok: false, error: String(error) });
  }
}

function ensureHeader(sheet) {
  if (sheet.getLastRow() > 0) return;

  sheet.appendRow([
    "Reference",
    "Submitted at",
    "Type",
    "Category",
    "Subject",
    "Message",
    "Priority",
    "Contact preference",
    "Rating",
    "Status",
    "Locale",
    "Source path",
    "User ID",
    "First name",
    "Last name",
    "Email",
    "Phone",
    "Role",
  ]);
  sheet.getRange(1, 1, 1, 18).setFontWeight("bold");
  sheet.setFrozenRows(1);
}

function safeCell(value) {
  const text = value == null ? "" : String(value);
  return /^[=+\-@]/.test(text) ? `'${text}` : text;
}

function jsonResponse(payload) {
  return ContentService.createTextOutput(JSON.stringify(payload)).setMimeType(
    ContentService.MimeType.JSON,
  );
}
