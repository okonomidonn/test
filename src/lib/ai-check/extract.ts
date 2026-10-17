// AIの回答本文から推薦店舗を拾い、対象店舗・競合店舗の推薦有無と順位を判定する。

export function normalize(s: string): string {
  return s.normalize("NFKC").toLowerCase().replace(/[\s　・*「」『』"'’]/g, "");
}

// 太字・見出し・番号付きリスト・箇条書きの先頭を、店名候補として出現順に拾う。
// 回答の書式はAIごとに違うため簡易的な抽出で、回答全文は別途保存して目視で確認できるようにする。
export function extractShops(text: string): string[] {
  const names: string[] = [];
  const re = /\*\*([^*\n]{2,40})\*\*|^\s*(?:#+|\d+[.)．]|[-・*])\s*([^\n:：（(|]{2,30})/gm;
  for (const m of text.matchAll(re)) {
    let name = (m[1] ?? m[2] ?? "").replace(/\*/g, "").trim();
    name = name.replace(/^\d+[.)．]\s*/, "").replace(/^[「『]|[」』:：]$/g, "").trim();
    if (name.length >= 2 && !names.includes(name)) names.push(name);
  }
  return names;
}

function matches(candidate: string, keys: string[]): boolean {
  const c = normalize(candidate);
  return keys.some((k) => k.length >= 2 && (c.includes(k) || (c.length >= 3 && k.includes(c))));
}

// 推薦順位（1始まり）。一覧に無いが本文で触れていれば 0、触れていなければ null。
export function rankOf(names: string[], shops: string[], text: string): number | null {
  const keys = names.map(normalize).filter((k) => k.length >= 2);
  if (keys.length === 0) return null;
  const idx = shops.findIndex((s) => matches(s, keys));
  if (idx >= 0) return idx + 1;
  const body = normalize(text);
  return keys.some((k) => body.includes(k)) ? 0 : null;
}

export function competitorHits(competitors: string[], shops: string[], text: string): string[] {
  return competitors.filter((c) => rankOf([c], shops, text) !== null);
}

export function domainOf(source: string): string {
  try {
    return new URL(source).hostname.replace(/^www\./, "");
  } catch {
    return source;
  }
}
