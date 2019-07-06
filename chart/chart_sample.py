
import datetime
import numpy as np
from sqlalchemy import func

from pygooglechart import Axis, Chart
from pygooglechart import XYLineChart

class OgiDataChart:
  def __init__(self, table_model, tableinfo, db):

    self.tables = {}
    for col_info in tableinfo['columns']:
      name = col_info['name']
      name_db = col_info['name_db']
      self.tables[name] = getattr(table_model, name_db)

    self.charts = []
    self.charts.append(self.createchart(db))

  def createchart(self, db):
    Width = 400
    Height = 250

    data = db.session.query(self.tables['X'], self.tables['Y']).\
      order_by(self.tables['X']).\
      filter(self.tables['Y']>0).all()

    x = [x[0] for x in data]
    y = [x[1] for x in data]


    chart = XYLineChart(Width, Height, x_range=(0, 10), y_range=(0, 10))
    chart.add_data(x)
    chart.add_data(y)
    chart.set_axis_labels(Axis.BOTTOM,
      [0, 5, 10])
    chart.set_axis_labels(Axis.LEFT, [0, 10])

    # 折れ線の色の設定
    # 16進数表記で与える
    chart.set_colours(['333333'])

    # 線種を設定
    # index は系列の番号 thickness は太さ
    chart.set_line_style(index=0, thickness=3)

    # グラフのタイトルの追加
    chart.set_title('CHART_Sample')

    return chart

  def geturls(self):
    return [c.get_url() for c in self.charts]

