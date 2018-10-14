#!/usr/bin/env python3
# coding:utf-8

#https://qiita.com/shirakiya/items/0114d51e9c189658002e

from ogidata.app import app
from ogidata.database import db

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

port = 8000
manager.add_command("runserver", Server(host='0.0.0.0', port=port))

if __name__ == '__main__':
    manager.run()
