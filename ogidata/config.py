
##### ogidata/config.py
"""FlaskのConfigを提供する"""
import os


class DevelopmentConfig:

  # Flask
  DEBUG = True

  # SQLAlchemy
  SQLALCHEMY_DATABASE_URI = 'mysql+pymysql://{user}:{password}@{host}/ogidatadb?charset=utf8'.format(**{
    'user': 'root',
    'password': '',
    'host': 'localhost',
  })
  SQLALCHEMY_TRACK_MODIFICATIONS = False
  SQLALCHEMY_ECHO = False


Config = DevelopmentConfig

