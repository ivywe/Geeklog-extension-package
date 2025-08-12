#!/bin/bash
# 安全にGit lockファイルを削除してコミット＆プッシュするスクリプト
# 使い方: ./safe_commit.sh "コミットメッセージ"

set -e  # エラーが出たら中断

# === 1. Gitの作業中プロセス確認 ===
if pgrep -f "git" >/dev/null; then
    echo "⚠️ 他でGitが動いています。処理を中断します。"
    exit 1
fi

# === 2. Gitリポジトリの場所確認 ===
if [ ! -d ".git" ]; then
    echo "❌ .git フォルダがありません。Gitリポジトリで実行してください。"
    exit 1
fi

# === 3. 残っているlockファイルを削除 ===
LOCK_FILES=$(find .git -name "*.lock" -type f)
if [ -n "$LOCK_FILES" ]; then
    echo "🧹 残っているlockファイルを削除します..."
    echo "$LOCK_FILES" | xargs rm -f
else
    echo "✅ lockファイルはありません。"
fi

# === 4. Gitステータス確認 ===
git status

# === 5. コミット ===
if [ -z "$1" ]; then
    echo "❌ コミットメッセージを指定してください。"
    echo "例: ./safe_commit.sh \"fix: バグ修正\""
    exit 1
fi

git add .
git commit -m "$1"

# === 6. プッシュ ===
git push

echo "🎉 コミット＆プッシュ完了"
