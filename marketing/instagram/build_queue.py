# 投稿の順番（queue.json）を作る。画像は wordpress-theme/zaito/sns/<id>/NN.jpg、キャプションは各カルーセルの キャプション.md から取る。
# すでに投稿済みのもの（posted が入っているもの）は、作り直しても残す。
import json, os, re, glob, hashlib
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
SNS = os.path.join(ROOT, 'wordpress-theme', 'zaito', 'sns')
CAR = os.path.join(ROOT, 'marketing', 'carousel')
BASE = 'https://zaito-work.com/wp-content/themes/zaito/sns'

def blocks(md):
    """「## 見出し（<フォルダ>/・…）」ごとに、最初のコードブロックをキャプションとして取り出す。"""
    out = {}
    for sec in re.split(r'\n(?=## )', md):
        m = re.search(r'（((?:jobs|about)/[a-z0-9]+)/', sec)
        c = re.search(r'```\n(.*?)\n```', sec, re.S)
        if m and c:
            out[m.group(1).replace('/', '-')] = c.group(1)
    return out

caps = {}
caps.update(blocks(open(os.path.join(CAR, 'jobs', 'キャプション.md'), encoding='utf-8').read()))
caps.update(blocks(open(os.path.join(CAR, 'about', 'キャプション.md'), encoding='utf-8').read()))
intro = re.search(r'```\n(.*?)\n```', open(os.path.join(CAR, 'キャプション.md'), encoding='utf-8').read(), re.S).group(1)
caps['intro'] = intro

# 紹介系と求人紹介を交互に並べる
ORDER = ['intro', 'jobs-gakusei', 'about-faq', 'jobs-mikeiken', 'about-tsukaikata', 'jobs-pay1500', 'jobs-tanjikan', 'jobs-jimu']
qpath = os.path.join(os.path.dirname(__file__), 'queue.json')
old = {p['id']: p for p in json.load(open(qpath, encoding='utf-8'))} if os.path.exists(qpath) else {}
queue = []
for pid in ORDER:
    imgs = sorted(glob.glob(os.path.join(SNS, pid, '*.jpg')))
    assert 2 <= len(imgs) <= 10, (pid, len(imgs))
    assert pid in caps, pid
    # Instagramは同じURLの画像を前に取りに来たときのまま使うことがあるので、中身が変わればURLも変わるように ?v= を付ける
    ver = lambda f: hashlib.md5(open(f, 'rb').read()).hexdigest()[:10]
    item = {'id': pid, 'images': [f'{BASE}/{pid}/{os.path.basename(i)}?v={ver(i)}' for i in imgs], 'caption': caps[pid]}
    if pid in old and old[pid].get('posted'):
        item.update({k: old[pid][k] for k in ('posted', 'media_id', 'permalink') if k in old[pid]})
    queue.append(item)
json.dump(queue, open(qpath, 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
print(len(queue), 'posts;', sum(1 for q in queue if q.get('posted')), 'already posted')
