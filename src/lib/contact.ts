// 公開する連絡先（特商法の表記・報告書で共用）。環境変数で上書きできる
export const CONTACT = {
  seller: process.env.LEGAL_SELLER_NAME || "AIススメル",
  representative: process.env.LEGAL_REPRESENTATIVE || "鳥居潤",
  email: process.env.LEGAL_EMAIL || "info@aisusumeru.com",
};
