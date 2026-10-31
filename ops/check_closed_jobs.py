#!/usr/bin/env python3
"""zaitoのまとめ求人（wordpress-theme/zaito/inc/remote-jobs.php）の募集ページを開き、募集が終わっていそうな求人を一覧にする。

ファイルは書き換えない。結果を見て、終わっている求人には 'closed' => true を付ける（毎日の定期チェックで使う）。
使い方: python3 ops/check_closed_jobs.py [--json]
"""
import json
import re
import subprocess
import sys
import time
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

JOBS = Path(__file__).resolve().parent.parent / 'wordpress-theme/zaito/inc/remote-jobs.php'

# 募集ページにこの言葉があれば、募集が終わっている可能性が高い。
CLOSED_WORDS = [
    '募集を終了', '募集は終了', '募集終了しました', '現在募集を停止', '募集を停止しています', '掲載を終了',
    '掲載は終了', '掲載期間が終了', 'この求人は現在募集しておりません', '現在、募集していません', '現在募集していません',
    'お探しのページは見つかりません', 'ページが見つかりません', 'This job is no longer available', 'no longer accepting',
    'この求人は現在非公開',
]


def jobs():
    src = JOBS.read_text(encoding='utf-8')
    for block in re.split(r"\n\s*array\(\n(?=\s*'slug')", src)[1:]:
        slug = re.search(r"'slug'\s*=>\s*'([^']+)'", block)
        url = re.search(r"'url'\s*=>\s*'([^']+)'", block)
        if not slug or not url:
            continue
        if re.search(r"'closed'\s*=>\s*true", block) or re.search(r"'excluded'\s*=>", block):
            continue
        yield slug.group(1), url.group(1)


def fetch(url):
    out = subprocess.run(
        ['curl', '-sL', '--max-time', '25', '-A', 'Mozilla/5.0 (zaito job check)', '-w', '\n%{http_code} %{url_effective}', url],
        capture_output=True, timeout=40,
    ).stdout.decode('utf-8', 'replace')
    body, _, tail = out.rpartition('\n')
    code, _, final = tail.partition(' ')
    return body, code, final


def check(item):
    slug, url = item
    try:
        body, code, final = fetch(url)
        # ハープキャリアは、公開中の求人にもときどき一時的に404を返す。少し待って取り直し、2回とも404なら終了とみなす。
        if code in ('404', '410'):
            time.sleep(5)
            body, code, final = fetch(url)
    except subprocess.TimeoutExpired:
        return slug, url, 'error', 'timeout'
    if code in ('404', '410'):
        return slug, url, 'closed', 'HTTP ' + code + '（2回とも）'
    # ハープキャリアは、非公開になった求人を403で返し、本文に「現在非公開」と出す。
    if code == '403' and 'この求人は現在非公開' in body:
        return slug, url, 'closed', '「この求人は現在非公開」（HTTP 403）'
    if not code.startswith('2'):
        return slug, url, 'error', 'HTTP ' + code
    # ハープキャリアの求人ページがログイン画面に転送されるのは、求人が非公開になったとき。
    if '/careers/login' in final:
        return slug, url, 'closed', 'ログイン画面に転送（非公開）'
    # 画面に出る文字だけで判定する（ハープキャリアなどは、どのページにも「見つかりません」の文言がスクリプトの中に入っている）。
    text = re.sub(r'<(script|style|noscript|template)\b.*?</\1>', ' ', body, flags=re.S | re.I)
    text = re.sub(r'<[^>]+>', ' ', text)
    hits = [w for w in CLOSED_WORDS if w in text]
    if hits:
        return slug, url, 'closed', '「' + hits[0] + '」' + ('（転送先 ' + final + '）' if final != url else '')
    # 求人ページから一覧やトップへ転送されたら、求人が消えた可能性がある。
    if final.rstrip('/').count('/') < url.rstrip('/').count('/') - 1:
        return slug, url, 'maybe', '転送先 ' + final
    return slug, url, 'ok', ''


def main():
    items = list(jobs())
    with ThreadPoolExecutor(8) as ex:
        res = list(ex.map(check, items))
    if '--json' in sys.argv:
        print(json.dumps([dict(zip(('slug', 'url', 'status', 'note'), r)) for r in res], ensure_ascii=False, indent=1))
        return
    for st in ('closed', 'maybe', 'error'):
        rows = [r for r in res if r[2] == st]
        print(f'== {st} ({len(rows)})')
        for r in rows:
            print(f'{r[0]}\t{r[3]}\t{r[1]}')
    print(f'== ok ({sum(1 for r in res if r[2] == "ok")}) / 確認した求人 {len(res)}')


if __name__ == '__main__':
    main()
