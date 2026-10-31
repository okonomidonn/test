#!/bin/sh
# Google アナリティクス（GA4）の MCP サーバーを起動する（.mcp.json から呼ばれる）。
# 鍵はリポジトリに置かない。クラウド環境の環境変数 ZAITO_GA_SA_JSON_B64（サービスアカウントの鍵のJSONを base64 にした1行）から、
# 起動のたびに一時ファイルへ書き出して使う。権限は GA の「閲覧者」だけなので、読み取り専用（--allow-write は付けない）。
set -e
if [ -z "$ZAITO_GA_SA_JSON_B64" ]; then
  echo "ZAITO_GA_SA_JSON_B64 が設定されていません（クラウド環境の設定に入れてください）" >&2
  exit 1
fi
umask 077
key="${TMPDIR:-/tmp}/zaito-ga-key.json"
printf %s "$ZAITO_GA_SA_JSON_B64" | tr -d '\r\n ' | base64 -d > "$key"
export GOOGLE_APPLICATION_CREDENTIALS="$key"
export GOOGLE_CLOUD_PROJECT="${GOOGLE_CLOUD_PROJECT:-zaito-ga}"
# 公式の google-analytics-mcp を、確認したコミットに固定して使う。
exec uvx --from "git+https://github.com/googleanalytics/google-analytics-mcp@a7b483f2b02c1d6a4e5432217a6d8aad4fb85d2f" analytics-mcp
