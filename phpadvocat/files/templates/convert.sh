for file in *.txt; do
    iconv -f utf-8 -t iso-8859-15 "$file" -o "${file%.txt}.iso.txt"
done
