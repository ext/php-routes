import argparse
import re


def trim(src):
    return'\n'.join([line.strip().lstrip('*') for line in src.splitlines()])


def indent(src, amount):
    prefix = ' ' * amount
    return'\n'.join([prefix + line for line in src.splitlines()])


def is_function(target):
    return ' function ' in target


def is_public(target):
    return 'public ' in target


def is_variable(target):
    return not is_function(target)


def rstClass(target):
    return '.. php:class:: %s\n\n' % target


def rstFunction(target, doc):
    prototype = re.sub(r'(public|function )', '', target)
    doc = indent(doc, 6)
    return '  .. php:function:: {prototype}\n\n{doc}\n\n'.format(prototype=prototype, doc=doc)


def rstVariable(target, doc):
    prototype = re.sub(r'( ?=.*|public |static )', '', target)
    if '=' in target:
        default = re.sub(r'.*= ?', '', target)
        doc += '\n :default: ``%s``\n' % default
    doc = indent(doc, 6)
    return '  .. php:attr:: {prototype}\n\n{doc}\n\n'.format(prototype=prototype, doc=doc)


def transform(src):
    classname = re.search(r'class (.*)\n', src).group(1)
    yield classname + '\n'
    yield '=' * len(classname) + '\n\n'
    yield rstClass(classname)

    for match in re.findall(r'/\*\*\n(.*?)\*/\n(.*?)[;{]', src, re.MULTILINE | re.DOTALL):
        doc = trim(match[0])
        target = trim(match[1])
        if not is_public(target):
            continue
        if is_function(target):
            yield rstFunction(target, doc)
        elif is_variable(target):
            yield rstVariable(target, doc)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Simple tool to read php docstring and convert to rst')
    parser.add_argument('src', metavar='SRC', type=argparse.FileType('r'))
    parser.add_argument('dst', metavar='DST', type=argparse.FileType('w'))
    args = parser.parse_args()
    args.dst.write(''.join(transform(args.src.read())))
