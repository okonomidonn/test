"use client";

// 報告書を印刷する（ブラウザの印刷画面から「PDFに保存」もできる）
export function PrintButton() {
  return (
    <button type="button" onClick={() => window.print()} className="rounded-lg bg-brand px-5 py-2.5 font-bold text-white hover:bg-brand-dark print:hidden">
      印刷・PDFで保存
    </button>
  );
}
