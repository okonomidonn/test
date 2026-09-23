"use client";

import { useState } from "react";
import { upload } from "@vercel/blob/client";

export function VideoUpload({ name, defaultValue }: { name: string; defaultValue: string }) {
  const [url, setUrl] = useState(defaultValue);
  const [progress, setProgress] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function onFile(file: File) {
    setError(null);
    setProgress(0);
    try {
      const blob = await upload(`reels/${file.name}`, file, {
        access: "public",
        handleUploadUrl: "/api/blob/upload",
        multipart: file.size > 20 * 1024 * 1024,
        onUploadProgress: (p) => setProgress(p.percentage),
      });
      setUrl(blob.url);
    } catch (e) {
      setError(e instanceof Error ? e.message : "アップロードに失敗しました");
    } finally {
      setProgress(null);
    }
  }

  return (
    <div className="mt-1 space-y-2">
      <input type="hidden" name={name} value={url} />
      {url && <video src={url} controls className="max-h-72 rounded-lg bg-black" />}
      <div className="flex flex-wrap items-center gap-3">
        <label className="inline-flex cursor-pointer items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm hover:bg-slate-50">
          {url ? "動画を差し替える" : "動画を選択（MP4 / MOV）"}
          <input
            type="file"
            accept="video/mp4,video/quicktime"
            className="hidden"
            disabled={progress !== null}
            onChange={(e) => {
              const file = e.target.files?.[0];
              if (file) onFile(file);
              e.target.value = "";
            }}
          />
        </label>
        {progress !== null && (
          <div className="flex items-center gap-2 text-xs text-slate-500">
            <div className="h-2 w-40 rounded bg-slate-100">
              <div className="h-2 rounded bg-brand" style={{ width: `${progress}%` }} />
            </div>
            {Math.round(progress)}%
          </div>
        )}
        {url && progress === null && (
          <button type="button" onClick={() => setUrl("")} className="text-xs text-rose-600 hover:underline">
            動画を外す
          </button>
        )}
      </div>
      {error && <p className="text-xs text-rose-600">{error}</p>}
      <p className="text-xs text-slate-400">推奨: 縦型 9:16、3秒〜15分、300MBまで</p>
    </div>
  );
}
