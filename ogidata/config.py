
##### ogidata/config.py
"""FlaskのConfigを提供する"""
import os


class DevelopmentConfig:

  # Flask
  DEBUG = True

  # SQLAlchemy
  # SQLALCHEMY_DATABASE_URI = 'mysql+pymysql://UUU:PPP@HHH/DDD?charset=utf8mb4' # in instance
  SQLALCHEMY_TRACK_MODIFICATIONS = False
  SQLALCHEMY_ECHO = False

  # Japanese in JSONify
  JSON_AS_ASCII = False

  # image upload folder
  UPLOAD_FOLDER = '/tmp/ogidata/upload'
  # upload size max
  MAX_CONTEXT_LENGTH = 2000000

Config = DevelopmentConfig

