インストール
============

## 必要条件

全てのコンポーネントが正しく動作するためには、最低限、redis バージョン 2.6.12 が必要です。

## Composer パッケージを取得する

このエクステンションをインストールするのに推奨される方法は [composer](http://getcomposer.org/download/) によるものです。

下記のコマンドを実行してください。

```
php composer.phar require --prefer-dist yiisoft/yii2-redis
```

または、あなたの `composer.json` ファイルの `require` セクションに、下記を追加してください。

```
"yiisoft/yii2-redis": "~2.0.0"
```

## アプリケーションを構成する

このエクステンションを使用するためには、アプリケーション構成情報で Connection クラスを構成する必要があります。


```php
return [
    //....
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],
    ]
];
```
