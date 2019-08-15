#!/usr/bin/env python3
# coding:utf-8

#https://qiita.com/shirakiya/items/0114d51e9c189658002e

from ogidata.app import app
from ogidata.database import db
import ogidata.functions

'''
if __name__ == '__main__':
  port = 8000
  #db.drop_all()
  #db.create_all()
  app.run(host='0.0.0.0', port=port)
'''
from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from flask_script import Server, Manager
from flask_migrate import Migrate, MigrateCommand
migrate = Migrate(app, db)
manager = Manager(app)
manager.add_command('db', MigrateCommand)

host = '0.0.0.0'
port = 443
ssl_crt = app.config['SSL_CERT']
ssl_key = app.config['SSL_PRIVKEY']
manager.add_command("runserver", Server(host=host, port=port, ssl_crt=ssl_crt, ssl_key=ssl_key, threaded=True))

@manager.command
def cleardata():
  if input('clear all data? (y/N):') == 'y':
    ogidata.functions.clearData()

@manager.command
def importtables():
  print('import tables')
  ogidata.functions.importTables()

@manager.command
def importdata():
  print('import data')
  ogidata.functions.importData()

@manager.command
def exportdata():
  print('export data')
  ogidata.functions.exportData()

if __name__ == '__main__':
  manager.run()
