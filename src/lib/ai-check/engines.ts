// AIサービスに店名を含まない質問を投げ、回答本文と参照URLを取得する。
// 各リクエストは会話履歴を持たない独立した1問で、Web検索を有効にして実行する。

export type EngineKey = "openai" | "gemini" | "mock";

export type EngineAnswer = {
  model: string;
  text: string;
  sources: string[];
};

export const ENGINE_LABELS: Record<EngineKey, string> = {
  openai: "ChatGPT",
  gemini: "Gemini",
  mock: "テスト用",
};

// APIキーが環境変数にある、または GEO_ENGINES で明示されたエンジンを使う。
// （キーをプロキシ側で付与する環境では環境変数にキーが無いため、GEO_ENGINES で指定する）
export function enabledEngines(): EngineKey[] {
  const listed = (process.env.GEO_ENGINES ?? "")
    .split(",")
    .map((s) => s.trim())
    .filter(Boolean);
  const engines: EngineKey[] = [];
  if (process.env.OPENAI_API_KEY || listed.includes("openai")) engines.push("openai");
  if (process.env.GEMINI_API_KEY || listed.includes("gemini")) engines.push("gemini");
  // テスト用の固定回答は開発環境でのみ使える
  if (listed.includes("mock") && process.env.NODE_ENV !== "production") engines.push("mock");
  return engines;
}

async function postJson(url: string, body: unknown, headers: Record<string, string>) {
  const res = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json", ...headers },
    body: JSON.stringify(body),
    signal: AbortSignal.timeout(170_000),
  });
  const data = await res.json().catch(() => null);
  if (!res.ok) {
    const message = data?.error?.message ?? `HTTP ${res.status}`;
    throw new Error(`${res.status} ${message}`.slice(0, 500));
  }
  return data;
}

type OpenAIContent = { type: string; text?: string; annotations?: { url?: string }[] };
type OpenAIOutput = { type: string; content?: OpenAIContent[] };

async function askOpenAI(question: string): Promise<EngineAnswer> {
  const model = process.env.OPENAI_MODEL || "gpt-5.5";
  const key = process.env.OPENAI_API_KEY;
  const data = await postJson(
    "https://api.openai.com/v1/responses",
    { model, input: question, tools: [{ type: "web_search" }] },
    key ? { Authorization: `Bearer ${key}` } : {},
  );
  const texts: string[] = [];
  const sources: string[] = [];
  for (const item of (data.output ?? []) as OpenAIOutput[]) {
    for (const part of item.content ?? []) {
      if (part.type !== "output_text") continue;
      texts.push(part.text ?? "");
      for (const a of part.annotations ?? []) if (a.url) sources.push(a.url);
    }
  }
  return { model, text: texts.join("\n"), sources };
}

type GeminiChunk = { web?: { uri?: string; title?: string } };

async function askGemini(question: string): Promise<EngineAnswer> {
  const model = process.env.GEMINI_MODEL || "gemini-3.8-flash";
  const key = process.env.GEMINI_API_KEY;
  const data = await postJson(
    `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent`,
    { contents: [{ role: "user", parts: [{ text: question }] }], tools: [{ google_search: {} }] },
    key ? { "x-goog-api-key": key } : {},
  );
  const cand = data.candidates?.[0] ?? {};
  const text = (cand.content?.parts ?? []).map((p: { text?: string }) => p.text ?? "").join("\n");
  const chunks = (cand.groundingMetadata?.groundingChunks ?? []) as GeminiChunk[];
  // Geminiの参照URLは転送用URLなので、サイト名（title）を残す
  const sources = chunks.map((c) => c.web?.title || c.web?.uri || "").filter(Boolean);
  return { model, text, sources };
}

// 開発中に画面を確認するための固定回答。本番では enabledEngines() が返さない。
async function askMock(question: string): Promise<EngineAnswer> {
  const shops = ["サンプル酒場 駅前店", "テスト食堂", "見本屋"];
  const text = [
    `「${question}」の候補です。`,
    ...shops.map((s, i) => `${i + 1}. **${s}**: 駅から近く、落ち着いた雰囲気のお店です。`),
  ].join("\n");
  return { model: "mock", text, sources: ["https://example.com/a", "https://tabelog.com/sample"] };
}

export async function ask(engine: EngineKey, question: string): Promise<EngineAnswer> {
  if (engine === "openai") return askOpenAI(question);
  if (engine === "gemini") return askGemini(question);
  return askMock(question);
}
