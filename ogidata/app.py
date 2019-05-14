#!/usr/bin/env python3
# coding:utf-8

##### ogidata/app.py
"""flask appの初期化を行い、flask appオブジェクトの実体を持つ"""
from flask import Flask, json, jsonify, request

from ogidata.database import init_db
import ogidata.models
import ogidata.functions as functions
from ogidata.util import *

from flask.json import JSONEncoder
from datetime import datetime, date
import os

#IMG max 2MB
IMG_SIZE_MAX = 2000000

class MyJSONEncoder(JSONEncoder):
  def default(self, o):
    if isinstance(o, datetime):
      return o.strftime("%Y-%m-%d %H:%M:%S")
    elif isinstance(o, date):
      return o.strftime("%Y-%m-%d")
    return super().default(o)

def create_app():
  app = Flask(__name__, instance_relative_config=True)
  app.config.from_object('ogidata.config.Config')
  app.config.from_pyfile('config.py', silent=False)

  app.json_encoder = MyJSONEncoder

  init_db(app)

  return app

app = create_app()

@app.route("/robots.txt", methods=['GET'])
def robots():
  msg = 'User-agent:*\nDisallow:/'
  return msg, 400, {'Content-Type': 'text/plain'}

@app.route("/ogidata/api/createtable", methods=['POST'])
def api_createtable():
  try:
    title = request.form['title']
    cols = request.form['cols']
  except:
    return errormessage('required field not filled')
  try:
    cols = json.loads(cols)
  except:
    return errormessage('cols not JSON')
  return functions.api_createtable(title, cols)

@app.route("/ogidata/api/gettables", methods=['GET'])
def api_gettables():
  return functions.api_gettables()

@app.route("/ogidata/api/gettableid", methods=['GET'])
def api_gettableid():
  title = request.args.get('title')
  return functions.api_gettableid(title)

@app.route("/ogidata/api/gettableinfo", methods=['GET'])
def api_gettableinfo():
  title = request.args.get('title')
  return functions.api_gettableinfo(title)

@app.route("/ogidata/api/insertdata", methods=['POST'])
def api_insertdata():
  try:
    title = request.form['title']
    data = request.form['data']
  except:
    return errormessage('required field not filled')
  try:
    data = json.loads(data)
  except:
    return errormessage('data not JSON')
  return functions.api_insertdata(title, data)

@app.route("/ogidata/api/droptable", methods=['POST'])
def api_droptable():
  try:
    title = request.form['title']
  except:
    return errormessage('required field not filled')
  return functions.api_droptable(title)

@app.route("/ogidata/api/getdata", methods=['GET'])
def api_getdata():
  title = request.args.get('title')
  start_index = request.args.get('start_index', default=0, type=int)
  limit = request.args.get('limit', default=100, type=int)
  asc = request.args.get('asc', default='yes')
  asc = False if asc.lower() in ('false', 'no') else True
  return functions.api_getdata(title, start_index, limit, asc)

@app.route("/ogidata/api/updatedata", methods=['POST'])
def api_updatedata():
  try:
    title = request.form['title']
    data_id = request.form['data_id']
    data = request.form['data']
  except:
    return errormessage('required field not filled')
  try:
    data_id = int(data_id)
  except:
    return errormessage('data_id not int')
  try:
    data = json.loads(data)
  except:
    return errormessage('data not JSON')
  return functions.api_updatedata(title, data_id, data)

@app.route("/ogidata/api/deletedata", methods=['POST'])
def api_deletedata():
  try:
    title = request.form['title']
    data_id = request.form['data_id']
  except:
    return errormessage('required field not filled')
  try:
    data_id = int(data_id)
  except:
    return errormessage('data_id not int')
  return functions.api_deletedata(title, data_id)

@app.route("/ogidata/api/getchoice", methods=['GET'])
def api_getchoice():
  title = request.args.get('title')
  limit = request.args.get('limit', default=10, type=int)
  columns = request.args.get('columns', default=None)
  return functions.api_getchoice(title, columns, limit)

@app.route("/ogidata/api/uploadimage", methods=['POST'])
def api_uploadimage():
  if 'file' not in request.files:
    return errormessage('no file')
  file = request.files['file']
  if file.filename == '':
    return errormessage('no selected file')
  if not file:
    return errormessage('no file')
  #filename = os.path.join(app.config['UPLOAD_FOLDER'],
  #  secure_filename(file.filename))
  #file.save(filename)
  return functions.api_uploadimage(file)

@app.route("/ogidata/api/removeimage", methods=['POST'])
def api_removeimage():
  try:
    img_id = request.form['img_id']
  except:
    return errormessage('required field not filled')
  try:
    img_id = int(img_id)
  except:
    return errormessage('img_id not int')
  return functions.api_removeimage(img_id)

@app.route("/ogidata/api/getimageinfo", methods=['GET'])
def api_getimageinfo():
  try:
    img_id = request.args.get('img_id')
  except:
    return errormessage('required field not filled')
  try:
    img_id = int(img_id)
  except:
    return errormessage('img_id not int')
  return functions.api_getimageinfo(img_id)

@app.route("/ogidata/api/getchart", methods=['GET'])
def api_getchart():
  title = request.args.get('title')
  return functions.api_getchart(title)

