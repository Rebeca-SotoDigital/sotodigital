#!/usr/bin/env python3
"""Prepare unsigned Apple Wallet sources. No credentials required."""
import argparse
import json
import shutil
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PEOPLE = {
    'rebeca': ('Rebeca Muñoz Herreros', 'CEO & Co-fundadora', '+34 659 625 857'),
    'jonatan': ('Jónatan Domingo Lobejón', 'CCO & Co-fundador', '+34 693 420 125'),
}


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=Path, required=True)
    parser.add_argument('--pass-type-id', required=True)
    parser.add_argument('--team-id', required=True)
    args = parser.parse_args()
    if not args.pass_type_id.startswith('pass.'):
        parser.error('--pass-type-id debe empezar por pass.')
    for slug, (name, role, phone) in PEOPLE.items():
        dest = args.output / slug
        dest.mkdir(parents=True, exist_ok=True)
        url = f'https://soto-digital.com/tarjeta/{slug}/'
        data = {
            'formatVersion': 1,
            'passTypeIdentifier': args.pass_type_id,
            'teamIdentifier': args.team_id,
            'serialNumber': f'soto-digital-{slug}-v1',
            'organizationName': 'Soto Digital',
            'description': f'Tarjeta de visita de {name}',
            'backgroundColor': 'rgb(255, 255, 255)',
            'foregroundColor': 'rgb(7, 27, 61)',
            'labelColor': 'rgb(120, 84, 30)',
            'barcodes': [{'format': 'PKBarcodeFormatQR', 'message': url,
                          'messageEncoding': 'iso-8859-1', 'altText': 'Ver tarjeta digital'}],
            'generic': {
                'primaryFields': [{'key': 'name', 'label': 'TARJETA DE VISITA', 'value': name}],
                'secondaryFields': [{'key': 'role', 'label': 'SOTO DIGITAL', 'value': role}],
                'auxiliaryFields': [{'key': 'phone', 'label': 'TELÉFONO', 'value': phone}],
                'backFields': [
                    {'key': 'email', 'label': 'Email', 'value': f'{slug}@soto-digital.com'},
                    {'key': 'web', 'label': 'Tarjeta digital', 'value': url},
                    {'key': 'address', 'label': 'Dirección',
                     'value': 'Polígono de Raos · Calle Galera, 41\n39600 Maliaño, Cantabria'},
                ],
            },
        }
        (dest / 'pass.json').write_text(json.dumps(data, ensure_ascii=False, indent=2) + '\n')
        for asset in (Path(__file__).parent / 'assets').glob('*.png'):
            shutil.copyfile(asset, dest / asset.name)
        print(f'Fuentes sin firmar: {dest}')


if __name__ == '__main__':
    main()
