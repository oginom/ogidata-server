#!/usr/bin/env python3
# coding:utf-8

import csv
from datetime import datetime, date
from importlib import import_module
import json
import os

from flask import Flask, jsonify, request
import flask.json
from sqlalchemy import distinct, exc
from sqlalchemy.sql.expression import func

from ogidata.database import db
import ogidata.models
from ogidata.util import *

# サムネイル画像サイズ 64x64
THUMBNAIL_SIZE_WIDTH = 64
THUMBNAIL_SIZE_HEIGHT = 64

datatypes = [
  'INT',
  'DOUBLE',
  'DECIMAL',
  'STRING',
  'CATEGORY',
  'TAGS',
  'TIMESTAMP',
  'DATE',
  'IMG'
]
unittypes = [
  'NONE',
  'YEN'
]

col_format = {
  "type" : "dict",
  "unknownkey" : False,
  "contents" : [
    {
      "key" : "name",
      "required" : True,
      "format" : {
        "type" : "string"
      }
    },
    {
      "key" : "type",
      "required" : True,
      "format" : {
        "type" : "string",
        "enum_list" : datatypes
      }
    },
    {
      "key" : "type_detail",
      "format" : {
        "type" : "dict"
      }
    },
    {
      "key" : "unit",
      "default" : "NONE",
      "format" : {
        "type" : "string",
        "enum_list" : unittypes
      }
    }
  ]
}

img_detail_mimetypes = [
  "JPG",
  "PNG"
]

img_detail_format = {
  "type" : "dict",
  "unknownkey" : False,
  "contents" : [
    {
      "key" : "img_width",
      "format" : {
        "type" : "int",
        "int_min" : 1
      }
    },
    {
      "key" : "img_height",
      "format" : {
        "type" : "int",
        "int_min" : 1
      }
    },
    {
      "key" : "img_mimetype",
      "format" : {
        "type" : "string",
        "enum_list" : img_detail_mimetypes
      }
    }
  ]
}

def fitValue(val, dbtype, dbtype_detail):
  if type(val) is not str:
    raise ValueError('value is not string')
  if dbtype in ['INT', 'IMG']:
    return int(val)
  elif dbtype in ['DOUBLE', 'FLOAT']:
    return float(val)
  elif dbtype in ['STRING', 'LONGTEXT', 'CATEGORY', 'TAGS']:
    return val
  elif dbtype == 'TIMESTAMP':
    return datetime.strptime(val, '%Y-%m-%d %H:%M:%S')
  elif dbtype == 'DATE':
    return datetime.strptime(val, '%Y-%m-%d').date()
  else:
    raise ValueError('type %s doesn\'t exist' % dbtype)

def gettableid(title):
  try:
    tableids = db.session.query(ogidata.models.TableTitle.table_id).\
      filter(ogidata.models.TableTitle.title==title).all()
  except exc.SQLAlchemyError:
    raise ValueError('get tableid sql error')
  except Exception:
    raise ValueError('get tableid error')
  if len(tableids) != 1:
    raise ValueError('no such table')
  return tableids[0][0]

def gettableinfo(tablename):
  tableinfo_filename = os.path.dirname(os.path.abspath(__file__)) + \
    '/../tableinfo/' + tablename + '.json'
  if not os.path.exists(tableinfo_filename):
    raise ValueError('tableinfo file not found')
  try:
    with open(tableinfo_filename, mode='r') as f:
      tableinfo = f.read()
      tableinfo = flask.json.loads(tableinfo)
  except Exception as e:
    raise ValueError('tableinfo file error '+str(e))
  return tableinfo

def settableinfo(tablename, tableinfo):
  tableinfo_filename = os.path.dirname(os.path.abspath(__file__)) + \
    '/../tableinfo/' + tablename + '.json'
  with open(tableinfo_filename, mode='w') as f:
    f.write(flask.json.dumps(tableinfo))

def createtablemodel(tablename, cols_info):
  attrs = {
    '__tablename__' : tablename,
    'data_id' : db.Column(db.Integer, nullable=False, autoincrement=True,
                          primary_key=True),
    'created_at' : db.Column(db.DateTime, nullable=False,
                             default=datetime.now),
    'updated_at' : db.Column(db.DateTime, nullable=False,
                             default=datetime.now, onupdate=datetime.now),
  }
  for i, col_info in enumerate(cols_info):
    type_db = col_info['type']
    if type_db in ['STRING', 'CATEGORY', 'TAGS']:
      attrs[col_info['name_db']] = db.Column(db.Text(length=20000))
    elif type_db == 'LONGTEXT':
      attrs[col_info['name_db']] = db.Column(db.Text(length=10000000))
    elif type_db in ['INT', 'IMG']:
      attrs[col_info['name_db']] = db.Column(db.Integer)
    elif type_db in ['DOUBLE', 'FLOAT']:
      attrs[col_info['name_db']] = db.Column(db.Float)
    elif type_db == 'TIMESTAMP':
      attrs[col_info['name_db']] = db.Column(db.DateTime)
    elif type_db == 'DATE':
      attrs[col_info['name_db']] = db.Column(db.Date)
    else:
      raise ValueError('DB type '+type_db+' is unknown')
  def to_dict(self, cols_info=cols_info):
    ret = dict()
    for i, col_info in enumerate(cols_info):
      ret[col_info['name']] = getattr(self, col_info['name_db'])
    return ret
  attrs['to_dict'] = to_dict
  def to_list(self, cols_info=cols_info, detail=False):
    ret = list()
    if detail:
      ret.append(str(getattr(self, 'data_id')))
      ret.append(str(getattr(self, 'created_at')))
      ret.append(str(getattr(self, 'updated_at')))
    for i, col_info in enumerate(cols_info):
      ret.append(str(getattr(self, col_info['name_db'])))
    return ret
  attrs['to_list'] = to_list
  Model = type(tablename, (db.Model,), attrs)
  return Model

table_models = []
def gettablemodel(tablename):
  for m in table_models:
    if m.__name__ == tablename:
      return m
  try:
    tableinfo = gettableinfo(tablename)
    cols_info = tableinfo['columns']
    Model = createtablemodel(tablename, cols_info)
    table_models.append(Model)
  except Exception as e:
    raise ValueError('error: '+str(e))
  return Model

def getnextimageid():
  imgids = db.session.query(ogidata.models.ImageInfo.img_id).\
    order_by(ogidata.models.ImageInfo.img_id.desc()).limit(1).all()
  if len(imgids) == 0:
    return 1
  return imgids[0][0] + 1

def getTables(as_list=False):
  try:
    tables = db.session.query(
      ogidata.models.TableTitle.title,
      ogidata.models.TableTitle.table_id).\
      order_by(ogidata.models.TableTitle.updated_at.desc()).all()
  except exc.SQLAlchemyError:
    raise ValueError('get tables sql error')
  except Exception as e:
    raise ValueError('get tables error')
  if as_list:
    d = [[k, v] for (k, v) in tables]
  else:
    d = {k: v for (k, v) in tables}
  return d

def dropTable(tableid):
  tablename = 'table' + str(tableid)
  table_model = gettablemodel(tablename)

  #drop table
  table_model.__table__.drop(db.engine)

  #delete column in tabletitle DB
  try:
    tables = db.session.query(ogidata.models.TableTitle).\
      filter(ogidata.models.TableTitle.table_id==tableid).all()
  except Exception:
    raise ValueError('get tableid error')
  if len(tables) != 1:
    raise ValueError('no table found on table ID list')
  db.session.delete(tables[0])
  try:
    db.session.commit()
  except exc.SQLAlchemyError:
    raise ValueError('sql error')

  #delete tableinfo JSON file
  tableinfo_filename = os.path.dirname(os.path.abspath(__file__)) + \
    '/../tableinfo/' + tablename + '.json'
  os.remove(tableinfo_filename)

def setTableUpdated(tableid):
  try:
    table = db.session.query(ogidata.models.TableTitle).\
      filter(ogidata.models.TableTitle.table_id==tableid).one()
    table.updated_at = datetime.now()
  except Exception as e:
    print(e)

def clearData():
  try:
    print('clear tables')
    d = getTables()
    for title, table_id in d.items():
      print('clear table "' + title + '"')
      try:
        dropTable(table_id)
      except Exception as e:
        print(e)
    print('clear imgs')
    db.session.query(ogidata.models.ImageInfo).delete()
    db.session.commit()
  except Exception as e:
    print(e)

def importTables():
  tableinfo_dir = os.path.dirname(os.path.abspath(__file__)) + \
    '/../tableinfo/'
  filenames = os.listdir(tableinfo_dir)
  for filename in filenames:
    print(filename)
    try:
      if filename[:5] != 'table' or filename[-5:] != '.json':
        raise ValueError('not tableinfo file')
      tableid = int(filename[5:-5])
      with open(tableinfo_dir + filename, mode='r') as f:
        tableinfo = f.read()
        tableinfo = flask.json.loads(tableinfo)
        print(tableinfo)
        d = ogidata.models.TableTitle(tableinfo['title'], tableid)
        db.session.add(d)
        db.session.commit()
        ''' create Table start '''
        tablename = 'table' + str(tableid)
        Model = createtablemodel(tablename, tableinfo['columns'])
        table_models.append(Model)
        db.create_all()
        ''' end '''
    except Exception as e:
      print(str(e))
      db.session.rollback()

def importData():
  imp_dir = os.path.dirname(os.path.abspath(__file__)) + \
    '/../data_imp/'
  filenames = os.listdir(imp_dir)
  for filename in filenames:
    print(filename)
    try:
      if filename == 'img_info.csv':
        with open(imp_dir + filename, mode='r') as f:
          for line in f:
            sp = flask.json.loads(line.rstrip())
            img_id = sp[1]
            img_ext = sp[2]
            img_filename = sp[3]
            thumbnail_filename = sp[4]
            img_width = sp[5]
            img_height = sp[6]
            d = ogidata.models.ImageInfo(img_id, img_filename,
              thumbnail_filename, img_ext, img_width, img_height)
            d.posted_at = datetime.strptime(sp[0], '%Y-%m-%d %H:%M:%S')
            d.is_removed = (sp[7] == '1')
            db.session.add(d)
        db.session.commit()
        continue
      if filename == 'table_title.csv':
        continue
      if filename[:5] != 'table' or filename[-4:] != '.csv':
        raise ValueError('not impdata file')
      tablename = filename[:-4]
      tableinfo = gettableinfo(tablename)
      table_model = gettablemodel(tablename)
      col_types = []
      for col_info in tableinfo['columns']:
        ind = int(col_info['name_db'][3:])
        col_type_detail = dict()
        if 'type_detail' in col_info.keys():
          col_type_detail = col_info['type_detail']
        col_types.append((col_info['name_db'], ind, col_info['type'],
          col_type_detail))
      with open(imp_dir + filename, mode='r') as f:
        for line in f:
          sp = flask.json.loads(line.rstrip())
          #print(sp)
          d = table_model()
          d.created_at = datetime.strptime(sp[0], '%Y-%m-%d %H:%M:%S')
          d.data_id = int(sp[1])
          for name_db, ind, col_type, col_type_detail in col_types:
            val = fitValue(sp[ind+2], col_type, col_type_detail)
            setattr(d, name_db, val)
          db.session.add(d)
      db.session.commit()
    except Exception as e:
      print(str(e))
      db.session.rollback()

def exportData():
  exp_dir = os.path.dirname(os.path.abspath(__file__)) + '/../data_exp'
  try:
    d = getTables()
    for title, table_id in d.items():
      outfile = '%s/table%d.csv' % (exp_dir, table_id)
      try:
        exportTable(table_id, outfile)
      except Exception as e:
        print(e)
      print('exported table%d "%s"' % (table_id, title))
    exportTableModel(ogidata.models.ImageInfo,
      ogidata.models.ImageInfo.img_id, '%s/img_info.csv' % exp_dir)
    print('exported img_info');
    exportTableModel(ogidata.models.TableTitle,
      ogidata.models.TableTitle.table_id, '%s/table_title.csv' % exp_dir)
    print('exported table_title');
  except Exception as e:
    print(e)

def exportTable(tableid, filename):
  tablename = 'table' + str(tableid)
  table_model = gettablemodel(tablename)
  crit = table_model.data_id
  exportTableModel(table_model, crit, filename)

def exportTableModel(table_model, crit, filename):
  data = db.session.query(table_model).order_by(crit).all()
  with open(filename, 'w') as f:
    writer = csv.writer(f)
    for line in data:
      writer.writerow(line.to_list(detail=True))

########## API no kabe ##########

def api_createtable(title, cols):
  if type(cols) is not list:
    return errormessage('cols list has keys')
  cols_info = []
  for i, col in enumerate(cols):
    try:
      col_info = formatValue(col, col_format)
      if 'type_detail' in col_info:
        if col_info['type'] == 'IMG':
          col_detail = formatValue(col_info['type_detail'], img_detail_format)
          col_info['type_detail'] = col_detail
        else:
          #TODO: implement
          return errormessage('type_detail for type %s is not implemented'
            % col_info['type'])
    except Exception as e:
      return errormessage('error: '+str(e))
    col_info['name_db'] = 'col' + str(i)
    cols_info.append(col_info)
  if len(cols_info) == 0:
    return errormessage('no columns')

  d = ogidata.models.TableTitle(title)
  db.session.add(d)
  try:
    db.session.commit()
  except exc.SQLAlchemyError:
    return errormessage('sql error')
  
  try:
    tableid = gettableid(title)
  except Exception as e:
    return errormessage(str(e))

  ''' create Table start '''
  tablename = 'table' + str(tableid)
  try:
    Model = createtablemodel(tablename, cols_info)
    table_models.append(Model)
    db.create_all()
  except Exception as e:
    return errormessage('error: '+str(e))
  ''' end '''

  tablename = 'table' + str(tableid)
  tableinfo = {
    'title' : title,
    'columns' : cols_info
  }
  settableinfo(tablename, tableinfo)

  return jsonify(d.to_dict()), 201

def api_gettables(as_list=True):
  try:
    d = getTables(as_list=as_list)
  except Exception as e:
    return errormessage(str(e))
  return jsonify(d), 200
  #return errormessage('notimplemented')

def api_gettableid(title):
  try:
    tableid = gettableid(title)
  except Exception as e:
    return errormessage(str(e))
  return jsonify(tableid), 200

def api_gettableinfo(title):
  try:
    tableid = gettableid(title)
    tablename = 'table' + str(tableid)
    tableinfo = gettableinfo(tablename)
  except Exception as e:
    return errormessage(str(e))
  return jsonify(tableinfo), 200

def api_insertdata(title, data):
  try:
    tableid = gettableid(title)
    tablename = 'table' + str(tableid)
    tableinfo = gettableinfo(tablename)
    table_model = gettablemodel(tablename)
  except Exception as e:
    return errormessage(str(e))
  cols_info = tableinfo['columns']

  #format
  add_cols = []
  d = table_model()
  for k,v in data.items():
    if type(k) is not str:
      return errormessage('data dict key is not string')
    name_db = ''
    col_type = ''
    col_type_detail = dict()
    for i, col_info in enumerate(cols_info):
      if k == col_info['name']:
        name_db = col_info['name_db']
        col_type = col_info['type']
        if 'type_detail' in col_info.keys():
          col_type_detail = col_info['type_detail']
        break
    if name_db == '':
      return errormessage('column %s not found' % k)
    if name_db in add_cols:
      return errormessage('column %s doubled' % k)

    v = str(v)
    try:
      val = fitValue(str(v), col_type, col_type_detail)
      setattr(d, name_db, val)
    except Exception as e:
      return errormessage(str(e))
    add_cols.append(name_db)

  #TODO required column check

  if len(add_cols) == 0:
    return errormessage('no columns')

  #return errormessage(str(d.col1)+' '+str(d.col2))

  db.session.add(d)
  setTableUpdated(tableid)
  try:
    db.session.commit()
  except exc.SQLAlchemyError as e:
    #TODO something
    return errormessage('sql error')

  ret = {
    'result' : 'success',
    'data' : d.to_dict()
  }
  return jsonify(ret), 201

def api_droptable(title):
  try:
    tableid = gettableid(title)
    dropTable(tableid)
  except Exception as e:
    return errormessage(str(e))

  ret = {'result' : 'success'}
  return jsonify(ret), 200

def api_getdata(title, start_index, limit, asc):
  if limit > 500:
    return errormessage('limit can\'t bigger than 500')
  try:
    tableid = gettableid(title)
    tablename = 'table' + str(tableid)
    table_model = gettablemodel(tablename)
  except Exception as e:
    return errormessage(str(e))
  crit = table_model.created_at
  if not asc:
    crit = crit.desc()
  data = db.session.query(table_model).order_by(crit).offset(start_index).\
    limit(limit).all()
  data_list = [line.to_list() for line in data]
  return jsonify(data_list)

def api_updatedata(title, data_id, data):
  try:
    tableid = gettableid(title)
    tablename = 'table' + str(tableid)
    tableinfo = gettableinfo(tablename)
    table_model = gettablemodel(tablename)
  except Exception as e:
    return errormessage(str(e))
  cols_info = tableinfo['columns']

  try:
    ds = db.session.query(table_model).\
      filter(table_model.data_id==data_id).all()
  except exc.SQLAlchemyError:
    raise ValueError('find data sql error')
  except Exception:
    raise ValueError('find data error')
  if len(ds) != 1:
    raise ValueError('no such data')
  d = ds[0]

  #format
  upd_cols = []
  for k,v in data.items():
    if type(k) is not str:
      return errormessage('data dict key is not string')
    name_db = ''
    col_type = ''
    col_type_detail = dict()
    for i, col_info in enumerate(cols_info):
      if k == col_info['name']:
        name_db = col_info['name_db']
        col_type = col_info['type']
        if 'type_detail' in col_info.keys():
          col_type_detail = col_info['type_detail']
        break
    if name_db == '':
      return errormessage('column %s not found' % k)
    if name_db in upd_cols:
      return errormessage('column %s doubled' % k)
    v = str(v)
    try:
      val = fitValue(str(v), col_type, col_type_detail)
      setattr(d, name_db, val)
    except Exception as e:
      return errormessage(str(e))
    upd_cols.append(name_db)

  if len(upd_cols) == 0:
    return errormessage('no updatable columns')

  try:
    db.session.commit()
  except exc.SQLAlchemyError as e:
    #TODO something
    return errormessage('sql error')

  ret = {
    'result' : 'success',
    'data' : d.to_dict()
  }
  return jsonify(ret), 200

def api_deletedata(title, data_id):
  try:
    tableid = gettableid(title)
    tablename = 'table' + str(tableid)
    tableinfo = gettableinfo(tablename)
    table_model = gettablemodel(tablename)
  except Exception as e:
    return errormessage(str(e))

  try:
    ds = db.session.query(table_model).\
      filter(table_model.data_id==data_id).all()
  except exc.SQLAlchemyError:
    raise ValueError('find data sql error')
  except Exception:
    raise ValueError('find data error')
  if len(ds) != 1:
    raise ValueError('no such data')
  d = ds[0]

  db.session.delete(d)
  try:
    db.session.commit()
  except exc.SQLAlchemyError:
    return errormessage('sql error')

  ret = {
    'result' : 'success',
  }
  return jsonify(ret), 200

def api_getchoice(title, columns, limit):
  try:
    tableid = gettableid(title)
    tablename = 'table' + str(tableid)
    tableinfo = gettableinfo(tablename)
    table_model = gettablemodel(tablename)
  except Exception as e:
    return errormessage(str(e))
  choices = {}
  for col in tableinfo['columns']:
    colname = col['name']
    name_db = col['name_db']
    col_model = getattr(table_model, name_db)
    max_data_id = func.max(table_model.data_id)
    choice = db.session.query(col_model, max_data_id).\
      group_by(col_model).order_by(max_data_id.desc()).limit(limit).all()
    choice = [str(x[0]) for x in choice]
    choices[colname] = choice
  return jsonify(choices), 200

def api_uploadimage(file):
  data = file.read()
  try:
    img = Image.open(BytesIO(data))
    imginfo = image_info(img)
  except:
    return errormessage('image upload error')
  if not imginfo['img_valid']:
    return errormessage("image not PNG or JPG");
  try:
    img_id = getnextimageid()
  except Exception as e:
    return errormessage('image DB inner error '+str(e))
  img_filename = 'img-' + str(img_id) + '.' + imginfo['img_ext']
  img_width = imginfo['img_width']
  img_height = imginfo['img_height']
  img_ext = imginfo['img_ext']
  savefilename = os.path.dirname(os.path.abspath(__file__)) + \
    '/static/img/' + img_filename
  thumbnail_filename = 'thm-' + str(img_id) + '.' + imginfo['img_ext']
  thumbnail_savefilename = os.path.dirname(os.path.abspath(__file__)) + \
    '/static/img/' + thumbnail_filename

  d = ogidata.models.ImageInfo(img_id, img_filename,
    thumbnail_filename, img_ext, img_width, img_height)
  db.session.add(d)
  try:
    db.session.commit()
  except exc.SQLAlchemyError as e:
    return errormessage('image DB update failed')

  try:
    img.save(savefilename)
  except:
    db.session.delete(d)
    try:
      db.session.commit()
    except exc.SQLAlchemyError as e:
      return errormessage('image upload failed but data remaining')
    return errormessage('image upload failed')

  try:
    img_resize = img.resize((64, 64))
    img_resize.save(thumbnail_savefilename)
  except Exception as e:
    os.remove(savefilename)
    db.session.delete(d)
    try:
      db.session.commit()
    except exc.SQLAlchemyError as e:
      return errormessage('create thumbnail failed but data remaining')
    return errormessage('create thumbnail failed')
  ret = {
    'result': 'success',
    'img_id': img_id
  }
  return jsonify(ret), 200

def api_removeimage(img_id):
  try:
    ds = db.session.query(ogidata.models.ImageInfo).\
      filter(ogidata.models.ImageInfo.img_id==img_id).all()
  except exc.SQLAlchemyError:
    return errormessage('find img sql error')
  except Exception:
    return errormessage('find img error')
  if len(ds) != 1:
    return errormessage('no such image ID')
  d = ds[0]
  if d.is_removed:
    return errormessage('already removed')
  rm_filename = os.path.dirname(os.path.abspath(__file__)) + \
    '/static/img/' + d.img_filename
  rm_thm_filename = os.path.dirname(os.path.abspath(__file__)) + \
    '/static/img/' + d.thumbnail_filename

  d.is_removed = True
  try:
    db.session.commit()
  except exc.SQLAlchemyError:
    return errormessage('sql error')
  os.remove(rm_filename)
  os.remove(rm_thm_filename)

  ret = {
    'result' : 'success',
  }
  return jsonify(ret), 200

def api_getimageinfo(img_id):
  try:
    ds = db.session.query(ogidata.models.ImageInfo).\
      filter(ogidata.models.ImageInfo.img_id==img_id).all()
  except exc.SQLAlchemyError:
    return errormessage('find img sql error')
  except Exception:
    return errormessage('find img error')
  if len(ds) != 1:
    return errormessage('no such image ID')
  d = ds[0]
  if d.is_removed:
    return errormessage('already removed')
  return jsonify(d.as_dict()), 200

def api_getchart(title):
  try:
    tableid = gettableid(title)
    tablename = 'table' + str(tableid)
    tableinfo = gettableinfo(tablename)
    table_model = gettablemodel(tablename)
  except Exception as e:
    return errormessage(str(e))
  chart_mod_name = 'chart.chart'+str(tableid)
  chart_mod_filename = os.path.dirname(os.path.abspath(__file__)) + \
    '/../chart/chart' + str(tableid) + '.py'
  if os.path.exists(chart_mod_filename):
    chart_mod = import_module(chart_mod_name)
    odc = chart_mod.OgiDataChart(table_model, tableinfo, db)
    urls = odc.geturls()
  else:
    urls = []
  ret = {'urls' : urls, 'tableid':tableid}
  return jsonify(ret), 200

