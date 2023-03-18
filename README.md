# ホテル予約システム (作成日: 2022/2/21)

ホテルの予約管理を行うWebアプリケーションです。

# 特徴

利用者画面と管理者画面で分かれており、それぞれ下記特徴があります。

* 利用者画面
  * カレンダー表示の予約画面などシンプルで使いやすいデザインになっています。
* 管理者画面
  * 予約状況を一目で確認でき、Excelで使えるようCSV形式でデータをダウンロード機能などがあります。


# インストール
1. このリポジトリをクローンします。
```git
$ git clone https://github.com/furaidopoteto/YoyakuApp.git
```
2. dockerでコンテナを立ち上げます。
```
$ cd YoyakuApp
$ docker-compose -f .yoyaku_docker/docker-compose.yml up -d
```
3. コンテナが立ち上がったら「[http://127.0.0.1:8080](http://127.0.0.1:8080)」にアクセスすることで使用できます。

# 使用しているライブラリ

このプロジェクトでは、以下のライブラリを使用しています。

- [jQuery](https://jquery.com/) - JavaScriptのライブラリ (MIT License)
- [Font Awesome](https://fontawesome.com/) - アイコンのフォント (MIT License)

それぞれのライブラリについては、各ライブラリのウェブサイトをご参照ください。