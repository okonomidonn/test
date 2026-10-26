// npm install --no-save playwright && npx playwright install chromium
// Optional: PLAYWRIGHT_MODULE_PATH (absolute package path), PDF_BROWSER_CHANNEL=msedge
import { createRequire } from "node:module";
import { fileURLToPath, pathToFileURL } from "node:url";
import path from "node:path";

const require = createRequire(import.meta.url);
const { chromium } = require(process.env.PLAYWRIGHT_MODULE_PATH || "playwright");
const directory = path.dirname(fileURLToPath(import.meta.url));
const browser = await chromium.launch({ headless: true, ...(process.env.PDF_BROWSER_CHANNEL ? { channel: process.env.PDF_BROWSER_CHANNEL } : {}) });
try {
  const page = await browser.newPage();
  for (const name of ["web_price_list", "zaito_case_study"]) {
    await page.goto(pathToFileURL(path.join(directory, `${name}.html`)).href, { waitUntil: "networkidle" });
    await page.evaluate(() => document.fonts.ready);
    await page.pdf({ path: path.join(directory, `${name}.pdf`), format: "A4", printBackground: true, preferCSSPageSize: true, tagged: true });
    console.log(`${name}.pdf generated`);
  }
} finally {
  await browser.close();
}
