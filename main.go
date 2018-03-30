package main

import (
	"html/template"
	"io"
	"net/http"

	"github.com/labstack/echo"
)

// レイアウト適用済のテンプレートを保存するmap
var templates map[string]*template.Template

// HTMLテンプレートを利用するためのRenderer Interface
type Template struct {
}

// HTMLテンプレートにデータを埋め込んだ結果をWriterに書き込む
func (t *Template) Render(w io.Writer, name string, data interface{}, c echo.Context) error {
	return templates[name].ExecuteTemplate(w, "layout.html", data)
}

func main() {
	e := echo.New()

	t := &Template{}
	e.Renderer = t

	// 静的ファイルのパスを設定
	e.Static("/css", "public/css")

	// 各ルーティングに対するハンドラを設定
	e.GET("/", Index)
	e.GET("/test", Test)
	e.Logger.Fatal(e.Start(":8081"))
}

// 初期化
func init() {
	loadTemplates()
}

// 各HTMLテンプレートに共通レイアウトを適用した結果を保存する
func loadTemplates() {
	var baseTemplate = "templates/layout.html"
	templates = make(map[string]*template.Template)
	templates["index"] = template.Must(
		template.ParseFiles(baseTemplate, "templates/index.html"),
)
	templates["test"] = template.Must(
		template.ParseFiles(baseTemplate, "templates/test.html"),
	)
}

func Index(c echo.Context) error {
	return c.Render(http.StatusOK, "index", "guys")
}

func Test(c echo.Context) error {
	return c.Render(http.StatusOK, "test", "")
}
