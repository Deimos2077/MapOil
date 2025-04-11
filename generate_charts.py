#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
import json
import uuid
import os
import mysql.connector
import pandas as pd
from bokeh.plotting import figure, output_file, save
from bokeh.models import ColumnDataSource, HoverTool, LabelSet, NumeralTickFormatter

# Параметры подключения к БД
db_config = {
    # 'host': '192.168.1.23',
    'host': 'localhost',
    # 'port': 3306,
    # 'user': 'user',
    'user': 'root',
    'password': '',
    # 'password': 'oil4815162342',
    'database': 'mapoil'
}

# Убедимся, что папка для графиков существует
charts_dir = "charts"
if not os.path.exists(charts_dir):
    os.makedirs(charts_dir)

def generate_charts(start_date, end_date):
    prefix = str(uuid.uuid4())
    graphs = {}

    # Функция для построения столбчатой диаграммы
    def generate_bar_chart(query, title, y_field, output_filename):
        conn = mysql.connector.connect(**db_config)
        df = pd.read_sql(query, conn, params=[start_date, end_date])
        conn.close()
        if df.empty:
            return "Нет данных"

        # Группируем данные и вычисляем среднее
        df_grouped = df.groupby('direction')[y_field].mean().reset_index()

        # Задаем порядок направлений
        order = ["Европа", "Китай", "Внутренний рынок"]
        df_grouped['direction'] = pd.Categorical(df_grouped['direction'], categories=order, ordered=True)
        df_grouped = df_grouped.sort_values('direction')

        # Задаем цвета для каждого направления
        color_map = {"Европа": "green", "Китай": "red", "Внутренний рынок": "#03c6fc"}
        df_grouped['color'] = df_grouped['direction'].map(color_map)

        # Форматируем значения для отображения в метках с разделителями тысяч
        df_grouped['formatted_value'] = df_grouped[y_field].apply(lambda x: f"{x:,.0f}")

        source = ColumnDataSource(df_grouped)
        directions = df_grouped['direction'].tolist()
        p = figure(x_range=directions, width=580, height=400, title=title, tools="hover")
        p.vbar(x='direction', top=y_field, width=0.7, source=source,
               fill_color='color', line_color='white')

        # Добавляем метки значений над столбцами
        labels = LabelSet(x='direction', y=y_field, text='formatted_value', level='glyph',
                          x_offset=0, y_offset=5, source=source, text_align='center', text_font_size="10pt")
        p.add_layout(labels)

        # Настраиваем ось Y для отображения полных чисел
        p.yaxis.formatter = NumeralTickFormatter(format="0,0")

        p.xaxis.axis_label = "Направление"
        p.yaxis.axis_label = y_field
        hover = p.select_one(HoverTool)
        if y_field == "final_price":
            hover.tooltips = [("Направление", "@direction"), ("Цена реализации нефти ($ за баррель)", f"@{y_field}{{0.2f}}")]
        elif y_field == "total_amount":
            hover.tooltips = [("Направление", "@direction"), ("Сумма реализации нефти ($)", f"@{y_field}{{0.2f}}")]
        elif y_field == "net_tons":
            hover.tooltips = [("Направление", "@direction"),
                              ("Объем реализации нефти (тонн нетто)", f"@{y_field}{{0.2f}}")]
        output_file(output_filename)
        save(p)
        return output_filename

    # 1) График: Объем реализации нефти по направлению (тонн нетто)
    query_net_tons = """
        SELECT date, net_tons, direction
        FROM shipments
        WHERE date BETWEEN %s AND %s
    """
    file1 = os.path.join(charts_dir, f"{prefix}_net_tons.html")
    result = generate_bar_chart(query_net_tons, "Объем реализации нефти (тонн нетто)", "net_tons", file1)
    graphs['net_tons'] = result

    # 2) График: Цена реализации ($ за баррель)
    query_final_price = """
        SELECT date, final_price, direction
        FROM shipments
        WHERE date BETWEEN %s AND %s
    """
    file2 = os.path.join(charts_dir, f"{prefix}_final_price.html")
    result = generate_bar_chart(query_final_price, "Цена реализации нефти ($ за баррель)", "final_price", file2)
    graphs['final_price'] = result

    # 3) График: Сумма реализации ($)
    query_total_amount = """
        SELECT date, total_amount, direction
        FROM shipments
        WHERE date BETWEEN %s AND %s
    """
    file3 = os.path.join(charts_dir, f"{prefix}_total_amount.html")
    result = generate_bar_chart(query_total_amount, "Сумма реализации нефти ($)", "total_amount", file3)
    graphs['total_amount'] = result

    return graphs

if __name__ == '__main__':
    if len(sys.argv) != 3:
        print(json.dumps({"error": "Требуются аргументы start_date и end_date"}))
        sys.exit(1)
    start_date = sys.argv[1]
    end_date = sys.argv[2]
    try:
        result = generate_charts(start_date, end_date)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({"error": str(e)}))