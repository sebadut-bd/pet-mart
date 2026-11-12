import { Dog, Cat, Bird, Fish, Rabbit, ShoppingBag, PawPrint, Turtle } from 'lucide-react';

const RULES = [
  [/dog|pupp/, Dog],
  [/cat|kitten/, Cat],
  [/bird|parrot/, Bird],
  [/fish|aqua/, Fish],
  [/rabbit/, Rabbit],
  [/reptile|turtle/, Turtle],
  [/suppl|accessor|food|cage/, ShoppingBag],
];

export function getCategoryIcon(slug = '') {
  const s = slug.toLowerCase();
  const match = RULES.find(([re]) => re.test(s));
  return match ? match[1] : PawPrint;
}
