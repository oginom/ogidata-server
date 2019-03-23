from flask import jsonify

from PIL import Image
from io import BytesIO

def errormessage(text):
  return jsonify({'ErrorMessage':text}), 400

def formatValue(raw, fmt):
  if fmt['type'] == 'dict':
    ret = {}
    if type(raw) is not dict:
      raise ValueError('not dict')
    unknownkey = fmt.get('unknownkey', True)
    contents = []
    if 'contents' in fmt and type(fmt['contents']) is list:
      contents = fmt['contents']
    if unknownkey:
      ret = raw
    else:
      for v in contents:
        if v['key'] in raw:
          ret[v['key']] = raw[v['key']]
    for v in contents:
      if 'default' in v:
        if v['key'] not in ret:
          #TODO: adapt to empty
          ret[v['key']] = v['default']
      required = bool(v.get('required', False))
      if v['key'] in ret:
        if 'format' in v:
          ret[v['key']] = formatValue(ret[v['key']], v['format'])
      else:
        if required:
          raise ValueError('value of key \'%s\' doesn\'t exists' % v['key'])
    return ret
  elif fmt['type'] == 'int':
    try:
      ret = int(raw)
    except:
      raise ValueError('int format error')
    if 'int_min' in fmt and ret < fmt['int_min']:
      raise ValueError('int out of range')
    if 'int_max' in fmt and ret > fmt['int_max']:
      raise ValueError('int out of range')
    return ret
  elif fmt['type'] == 'string':
    ret = str(raw)
    if 'enum_list' in fmt:
      if ret not in fmt['enum_list']:
        raise ValueError('enum not match')
    return ret
  else:
    raise ValueError('type \'%s\' unavailable' % fmt['type'])

IMG_EXTENSIONS = set(['png', 'jpg', 'jpeg'])
def image_info(img):
  ret = dict()
  #elif ret['mime'] in ['jpg', 'jpeg']:
  #  ret['img_valid'] = True
  #  ret['img_ext'] = 'jpg'
  if img.format in ['JPEG']:
    ret['img_valid'] = True
    ret['img_ext'] = 'jpg'
  elif img.format in ['PNG']:
    ret['img_valid'] = True
    ret['img_ext'] = 'png'
  else:
    ret['img_valid'] = False
    return ret
  ret['img_width'], ret['img_height'] = img.size
  return ret

