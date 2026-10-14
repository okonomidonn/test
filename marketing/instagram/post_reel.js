// リール動画を1本、zaito_work に投稿する。
// 使い方: node post_reel.js <A|B|C>
// 動画は wordpress-theme/zaito/sns/reels/<ID>.mp4（サイトに置いたもの）、投稿文は marketing/ads/phone/リール投稿文.md から取る。
// トークンは環境の「ネットワークシークレット」が付けるので、ここでは curl で普通に呼ぶだけ。
const cp = require('child_process');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const API = 'https://graph.instagram.com/v21.0';
const ROOT = path.join(__dirname, '..', '..');
const id = process.argv[2];
if (!/^[ABC]$/.test(id || '')) { console.error('使い方: node post_reel.js <A|B|C>'); process.exit(1); }

function curl(args) {
  const out = cp.execFileSync('curl', ['-sS', '--max-time', '120', ...args], { encoding: 'utf8' });
  let j;
  try { j = JSON.parse(out); } catch (e) { throw new Error('APIの返事を読めませんでした: ' + out.slice(0, 200)); }
  if (j.error) throw new Error('APIエラー: ' + (j.error.message || JSON.stringify(j.error)));
  return j;
}
const get = (p, q = {}) => curl([API + p + '?' + new URLSearchParams(q)]);
const post = (p, form) => curl(['-X', 'POST', API + p, ...Object.entries(form).flatMap(([k, v]) => ['--data-urlencode', `${k}=${v}`])]);
const sleep = ms => Atomics.wait(new Int32Array(new SharedArrayBuffer(4)), 0, 0, ms);

// 投稿文: 「## <ID>（」の節の最初のコードブロック
const md = fs.readFileSync(path.join(ROOT, 'marketing/ads/phone/リール投稿文.md'), 'utf8');
const sec = md.split(/\n(?=## )/).find(s => s.startsWith('## ' + id + '（'));
const caption = sec && (sec.match(/```\n([\s\S]*?)\n```/) || [])[1];
if (!caption) throw new Error('投稿文が見つかりません: ' + id);
const tags = (caption.match(/#[^\s#]+/g) || []).length;
if (tags > 5) throw new Error('ハッシュタグは5個までです（' + tags + '個）');

// 動画のURL。中身が変わればURLも変わるように ?v= を付ける（Instagramが古い動画を使わないように）
const file = path.join(ROOT, 'wordpress-theme/zaito/sns/reels', id + '.mp4');
const ver = crypto.createHash('md5').update(fs.readFileSync(file)).digest('hex').slice(0, 10);
const url = `https://zaito-work.com/wp-content/themes/zaito/sns/reels/${id}.mp4?v=${ver}`;
const head = cp.execFileSync('curl', ['-sS', '-o', '/dev/null', '-w', '%{http_code} %{content_type}', url], { encoding: 'utf8' });
if (!/^200 video\/mp4/.test(head)) throw new Error('動画が開けません: ' + url + ' → ' + head);

const me = get('/me', { fields: 'user_id,username' });
console.log('アカウント: ' + me.username + ' / リール' + id);
const box = post('/' + me.user_id + '/media', { media_type: 'REELS', video_url: url, caption, share_to_feed: 'true' }).id;

let status = '';
for (let i = 0; i < 60; i++) {
  status = get('/' + box, { fields: 'status_code' }).status_code;
  if (status === 'FINISHED') break;
  if (status === 'ERROR' || status === 'EXPIRED') throw new Error('動画の準備に失敗しました: ' + status);
  sleep(5000);
}
if (status !== 'FINISHED') throw new Error('動画の準備が終わりませんでした: ' + status);

const media = post('/' + me.user_id + '/media_publish', { creation_id: box }).id;
const link = get('/' + media, { fields: 'permalink' }).permalink;
const logp = path.join(__dirname, 'reels_posted.json');
const log = fs.existsSync(logp) ? JSON.parse(fs.readFileSync(logp, 'utf8')) : [];
log.push({ id, posted: new Date().toISOString(), media_id: media, permalink: link, video: url });
fs.writeFileSync(logp, JSON.stringify(log, null, 1));
console.log('投稿しました: ' + link);
